<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Mssql;

use Ntch\Pocoapoco\WebRestful\Models\Database\BaseInterface;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;
use function Couchbase\passthruEncoder;

class Base extends ModelBase implements BaseInterface
{

    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * @inheritDoc
     */
    public function execute(string $mvc)
    {
        foreach (self::$databaseList[$mvc]['mssql']['server'] as $serverName => $serverConfig) {
            $this->checkDriverConfig($serverName, $serverConfig);
            $conn = $this->connect($serverConfig);

            if ($conn) {
                self::$databaseList[$mvc]['mssql']['server'][$serverName]['connect']['status'] = 'success';
                self::$databaseList[$mvc]['mssql']['server'][$serverName]['connect']['result'] = $conn;
            } else {
                $errors = sqlsrv_errors();
                self::$databaseList[$mvc]['mssql']['server'][$serverName]['connect']['status'] = 'error';
                foreach ($errors as $error) {
                    self::$databaseList[$mvc]['mssql']['server'][$serverName]['connect']['code'] = $error['code'];
                    self::$databaseList[$mvc]['mssql']['server'][$serverName]['connect']['result'] = $error['message'];
                }

            }
        }
        isset(self::$databaseObject[$mvc]['mssql']->server) ? $this->loadModelUserSchema($mvc) : null;
    }

    /**
     * @inheritDoc
     */
    public function connect(array $driverConfig)
    {
        $user = $driverConfig['user'];
        $password = $driverConfig['password'];
        $ip = $driverConfig['ip'];
        $port = $driverConfig['port'];
        $database = $driverConfig['database'];
        $tns = "tcp:$ip,$port";
        $conn = @sqlsrv_connect($tns, ["Database" => $database, "UID" => $user, "PWD" => $password]);

        return $conn;
    }

    /**
     * @inheritDoc
     */
    public function checkDriverConfig(string $serverName, array $driver)
    {
        $driverConfigList = ['ip', 'port', 'database', 'user', 'password'];

        foreach ($driverConfigList as $key) {
            isset($driver[$key]) ? null : die("【ERROR】Model $serverName tag \"$key\" is not exist.");
        }
    }

    /**
     * @inheritDoc
     */
    public function loadModelUserSchema(string $mvc)
    {
        foreach (self::$databaseObject[$mvc]['mssql']->server as $serverName => $serverInfo) {
            if (self::$databaseList[$mvc]['mssql']['server'][$serverName]['connect']['status'] === 'success') {

                $allTabColumns = $this->allTabColumns($serverName, self::$databaseList[$mvc]['mssql']['server'][$serverName]['user'], $mvc);
                if ($allTabColumns['status'] === 'SUCCESS') {
                    for ($i = 0; $i < $allTabColumns['result']['total']; $i++) {
                        $tableName = $allTabColumns['result']['data'][$i]['TABLE_NAME'];
                        $columnName = $allTabColumns['result']['data'][$i]['COLUMN_NAME'];

                        isset(self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName) ? null : self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName = new \stdClass();
                        self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['DATA_TYPE'] = $allTabColumns['result']['data'][$i]['DATA_TYPE'];
                        $data_type = $allTabColumns['result']['data'][$i]['DATA_TYPE'];
                        switch ($data_type) {
                            case 'CHAR':
                            case 'NCHAR':
                            case 'VARCHAR2':
                            case 'NVARCHAR2':
                                self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = $allTabColumns['result']['data'][$i]['DATA_LENGTH'];
                                break;
                            case 'NUMBER':
                                $dataPercision = $allTabColumns['result']['data'][$i]['DATA_PRECISION'];
                                $dataScale = $allTabColumns['result']['data'][$i]['DATA_SCALE'];
                                self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = "$dataPercision,$dataScale";
                                break;
                            case strpos($data_type, 'TIMESTAMP'):
                            case 'DATE':
                            case 'NCLOB':
                                self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = null;
                                break;
                            case 'FLOAT':
                                self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = $allTabColumns['result']['data'][$i]['DATA_PRECISION'];
                                break;
                        }
                        self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['NULLABLE'] = $allTabColumns['result']['data'][$i]['IS_NULLABLE'];
                        self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['DATA_DEFAULT'] = str_replace(['(', ')', '\''], '', $allTabColumns['result']['data'][$i]['COLUMN_DEFAULT']);
                        self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['KEY_TYPE'] = $allTabColumns['result']['data'][$i]['CONSTRAINT_TYPE'];
                        self::$databaseObject[$mvc]['mssql']->server[$serverName]->$tableName->schema[$columnName]['COMMENT'] = $allTabColumns['result']['data'][$i]['COMMENT'];
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function allTabColumns(string $serverName, string $serachName, string $mvc)
    {
        $sql = <<<sqlCommand
            SELECT
                table1.*,
                table2.CONSTRAINT_NAME,
                table2.CONSTRAINT_TYPE
            FROM
                (
                    SELECT 
                        tab.name as TABLE_NAME,
                        col.name as COLUMN_NAME,
                        typ.name as DATA_TYPE,
                        col.length as CHARACTER_MAXIMUM_LENGTH,
                        col.prec as NUMERIC_PRECISION,
                        col.scale as NUMERIC_SCALE,
                        com.text as COLUMN_DEFAULT,
                        (
                            CASE 
                            WHEN col.isnullable = 1 THEN 'Y' 
                            ELSE 'N' 
                            END
                        ) as IS_NULLABLE,
                        (
                            SELECT VALUE 
                            FROM Fn_listextendedproperty (NULL, 'schema', 'dbo', 'table', tab.name, 'column', col.name) 
                        ) as COMMENT
                    FROM sysobjects tab,
                         syscolumns col 
                         LEFT OUTER JOIN syscomments com 
                         INNER JOIN sysobjects obj ON com.id = obj.id 
                         ON col.cdefault = com.id AND com.colid = 1, 
                         systypes typ
                    WHERE  tab.id = col.id 
                            AND tab.xtype = 'U' 
                            AND col.xusertype = typ.xusertype  
                ) as table1
            LEFT JOIN 
                (
                    SELECT 
                        tab.name as TABLE_NAME,
                        clmns.name as COLUMN_NAME,
                        constr.name as CONSTRAINT_NAME,
                        constr.xtype as CONSTRAINT_TYPE
                    FROM SysObjects AS tab
                    INNER JOIN sysobjects AS constr ON(constr.parent_obj = tab.id)
                    INNER JOIN sys.indexes AS i ON( (i.index_id > 0 and i.is_hypothetical = 0) AND (i.object_id=tab.id) AND i.name = constr.name )
                    INNER JOIN sys.index_columns AS ic ON (ic.column_id > 0 and (ic.key_ordinal > 0 or ic.partition_ordinal = 0 or ic.is_included_column != 0)) 
                                                AND (ic.index_id=CAST(i.index_id AS int) 
                                                AND ic.object_id=i.object_id)
                    INNER JOIN sys.columns AS clmns ON clmns.object_id = ic.object_id and clmns.column_id = ic.column_id
                ) as table2 ON table1.TABLE_NAME = table2.TABLE_NAME AND table1.COLUMN_NAME = table2.COLUMN_NAME
        sqlCommand;
        return self::query('server', $serverName, null, $sql, null, [], null, 0, -1, $mvc, false);
    }

    /**
     * @inheritDoc
     */
    public static function query(string $modelType, string $modelName, ?string $tableName, string $sqlCommand, ?array $sqlData, array $sqlData_bind, ?string $keyName, int $offset, int $limit, string $mvc, bool $query_pass)
    {
        // config
        $modelType === 'server' ? $serverName = $modelName : $serverName = self::$databaseList[$mvc]['mssql']['table'][$modelName]['server'];
        $conn = self::$databaseList[$mvc]['mssql']['server'][$serverName]['connect']['result'];

        // debug
        sqlsrv_configure("WarningsReturnAsErrors", 1);

        // transaction for commit and rollback
        sqlsrv_begin_transaction($conn);

        // response
        $action = explode(' ', strtoupper(trim($sqlCommand)))[0];
        $action = str_replace(["\r", "\n", "\r\n", "\n\r"], '', $action);
        $dbRows['action'] = $action;
        $dbRows['status'] = null;
        $dbRows['sql'] = null;
        $dbRows['result'] = null;

        // remove ';'
        $lastChar = substr(trim($sqlCommand), -1);
        $sqlCommand = trim($sqlCommand);
        if ($lastChar === ';') {
            $sqlCommand = substr($sqlCommand, 0, -1);
        }

        // avoid sql injection
        empty($sqlData) ? $sqlData = null : null;
        if (!is_null($sqlData)) {

            foreach ($sqlData as $key => $value) {
                $$key = $value;
                $sql_data[] = &$$key;
                $sqlCommand = preg_replace('/\?/', "'$value'", $sqlCommand, 1);
            }

            // parse
            $stmt = sqlsrv_prepare($conn, $sqlCommand, $sql_data);
        } else {
            // parse
            $stmt = sqlsrv_prepare($conn, $sqlCommand);
        }

        // result
        $error = sqlsrv_errors();
        if (!$error) {
            $dbRows['status'] = 'SUCCESS';

            switch ($action) {
                case 'SELECT':
                    $dbRows['result']['total'] = 0;

                    if (!is_null($sqlData)) {
                        $res = sqlsrv_query($conn, $sqlCommand, $sql_data);
                    } else {
                        $res = sqlsrv_query($conn, $sqlCommand);
                    }

                    if (is_null($keyName)) {
                        while ($row = @sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
                            $dbRows['result']['data'][$dbRows['result']['total']] = $row;
                            $dbRows['result']['total']++;
                        }
                    } else {
                        $keyName = strtoupper($keyName);
                        while ($row = @sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
                            $dbRows['result']['data'][$row[$keyName]] = $row;
                            $dbRows['result']['total']++;
                        }
                    }
                    break;
                case 'INSERT':
                case 'UPDATE':
                case 'DELETE':
                    @sqlsrv_execute($stmt);

                    if (substr(trim($action), -1) === 'E') {
                        $todo = strtolower($action) . 'd';
                    } else {
                        $todo = strtolower($action) . 'ed';
                    }
                    $rows = sqlsrv_rows_affected($stmt);
                    if (!$rows) {
                        $rows = 0;
                    }
                    $dbRows['result']['total'] = $rows;
                    $dbRows['result']['message'] = "$rows row(s) $todo.";
                    break;
                default:
                    die("【ERROR】Model is not support \"$action\".");
            }
        }

        $dbRows['sql'] = "\n$sqlCommand;";
        return $dbRows;

    }

    /**
     * @inheritDoc
     */
    public static function systemSet(string $action, array $schema, array $data)
    {
        foreach ($schema as $key => $value) {
            if (isset($value['SYSTEM_SET'])) {
                switch ($value['SYSTEM_SET']) {
                    case 'PRIMARY_KEY':
                        if ($action == 'INSERT') {
                            $data[$key] = self::sqlId();
                        }
                        break;
                    case 'UPDATE_DATE':
                        switch ($value['DATA_SIZE']) {
                            case 'YYYY-MM-DD':
                                $data[$key] = date('Y-m-d');
                                break;
                            case 'YYYY-MM-DD hh:mm:ss':
                                $data[$key] = date('Y-m-d H:i:s');
                                break;
                            default:
                                $data_size = $value['DATA_SIZE'];
                                die("【ERROR】Not support DATA_SIZE： $data_size");
                        }
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public static function dataBind(string $modelType, string $modelName, string $tableName, array $sqlData, string $mvc, bool $query_pass)
    {

    }

}