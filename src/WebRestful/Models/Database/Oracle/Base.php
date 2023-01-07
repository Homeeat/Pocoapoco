<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Oracle;

use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\BaseInterface;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;

class Base extends ModelBase implements BaseInterface
{

    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * @inheritDoc
     */
    public function execute(string $mvc)
    {
        foreach (self::$databaseList[$mvc]['oracle']['server'] as $serverName => $serverConfig) {
            $this->checkDriverConfig($serverName, $serverConfig);
            $conn = $this->connect($serverConfig);

            if ($conn) {
                self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['status'] = 'success';
                self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['result'] = $conn;
            } else {
                $error = oci_error();
                self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['status'] = 'error';
                self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['result'] = $error['message'];
            }
        }
        isset(self::$databaseObject[$mvc]['oracle']->server) ? $this->loadModelUserSchema($mvc) : null;
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
        $sid = $driverConfig['sid'];
        $tns = "//$ip:$port/$sid";
        $conn = @oci_pconnect($user, $password, $tns, 'AL32UTF8');

        return $conn;
    }

    /**
     * @inheritDoc
     */
    public function checkDriverConfig(string $serverName, array $driver)
    {
        $driverConfigList = ['ip', 'port', 'sid', 'user', 'password'];

        foreach ($driverConfigList as $key) {
            isset($driver[$key]) ? null : die("【ERROR】Model $serverName tag \"$key\" is not exist.");
        }
    }

    /**
     * @inheritDoc
     */
    public function loadModelUserSchema(string $mvc)
    {
        foreach (self::$databaseObject[$mvc]['oracle']->server as $serverName => $serverInfo) {
            if (self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['status'] === 'success') {

                $allTabColumns = $this->allTabColumns($serverName, self::$databaseList[$mvc]['oracle']['server'][$serverName]['user'], $mvc);
                if ($allTabColumns['status'] === 'SUCCESS') {
                    for ($i = 0; $i < $allTabColumns['result']['total']; $i++) {
                        $tableName = $allTabColumns['result']['data'][$i]['TABLE_NAME'];
                        $columnName = $allTabColumns['result']['data'][$i]['COLUMN_NAME'];

                        isset(self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName) ? null : self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName = new \stdClass();
                        self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['DATA_TYPE'] = $allTabColumns['result']['data'][$i]['DATA_TYPE'];
                        $data_type = $allTabColumns['result']['data'][$i]['DATA_TYPE'];
                        switch ($data_type) {
                            case 'CHAR':
                            case 'NCHAR':
                            case 'VARCHAR2':
                            case 'NVARCHAR2':
                                self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = $allTabColumns['result']['data'][$i]['DATA_LENGTH'];
                                break;
                            case 'NUMBER':
                                $dataPercision = $allTabColumns['result']['data'][$i]['DATA_PRECISION'];
                                $dataScale = $allTabColumns['result']['data'][$i]['DATA_SCALE'];
                                self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = "$dataPercision,$dataScale";
                                break;
                            case strpos($data_type, 'TIMESTAMP'):
                            case 'DATE':
                            case 'TIMESTAMP':
                            case 'TIMESTAMP WITH TIME ZONE':
                            case 'TIMESTAMP WITH LOCAL TIME ZONE':
                            case 'NCLOB':
                                self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = null;
                                break;
                            case 'FLOAT':
                                self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = $allTabColumns['result']['data'][$i]['DATA_PRECISION'];
                                break;
                        }
                        self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['NULLABLE'] = $allTabColumns['result']['data'][$i]['NULLABLE'];
                        self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['DATA_DEFAULT'] = $allTabColumns['result']['data'][$i]['DATA_DEFAULT'];
                        self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['KEY_TYPE'] = $allTabColumns['result']['data'][$i]['CONSTRAINT_TYPE'];
                        self::$databaseObject[$mvc]['oracle']->server[$serverName]->$tableName->schema[$columnName]['COMMENT'] = $allTabColumns['result']['data'][$i]['COMMENTS'];
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
            SELECT ATC.TABLE_NAME, ATC.COLUMN_NAME, ATC.DATA_TYPE, ATC.DATA_LENGTH, ATC.DATA_PRECISION, ATC.DATA_SCALE, ATC.NULLABLE, ATC.COLUMN_ID, ATC.DATA_DEFAULT, ACC.COMMENTS, CON.CONSTRAINT_NAME, CON.CONSTRAINT_TYPE
            FROM ALL_TAB_COLUMNS ATC
            LEFT JOIN ALL_COL_COMMENTS ACC ON CONCAT(ATC.TABLE_NAME, ATC.COLUMN_NAME) = CONCAT(ACC.TABLE_NAME, ACC.COLUMN_NAME)
            LEFT JOIN 
                (
                    SELECT CONS.CONSTRAINT_NAME, CONS.CONSTRAINT_TYPE, CONS.TABLE_NAME, COLS.COLUMN_NAME 
                    FROM ALL_CONSTRAINTS CONS 
                    LEFT JOIN ALL_CONS_COLUMNS COLS ON CONS.CONSTRAINT_NAME = COLS.CONSTRAINT_NAME 
                    WHERE CONS.CONSTRAINT_TYPE IN ('P')
                ) CON ON CONCAT(ACC.TABLE_NAME, ACC.COLUMN_NAME) = CONCAT(CON.TABLE_NAME, CON.COLUMN_NAME)
            WHERE ATC.OWNER = '$serachName'
            ORDER BY COLUMN_ID
        sqlCommand;
        return self::query('server', $serverName, null, $sql, null, [], null, 0, -1, $mvc, false);
    }

    /**
     * @inheritDoc
     */
    public static function query(string $modelType, string $modelName, ?string $tableName, string $sqlCommand, ?array $sqlData, array $sqlData_bind, ?string $keyName, int $offset, int $limit, string $mvc, bool $query_pass)
    {
        // config
        $modelType === 'server' ? $serverName = $modelName : $serverName = self::$databaseList[$mvc]['oracle']['table'][$modelName]['server'];
        $conn = self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['result'];

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

        // parse
        $stid = @oci_parse($conn, $sqlCommand);

        // avoid sql injection
        empty($sqlData) ? $sqlData = null : null;
        if (!is_null($sqlData)) {
            $sqlBind = self::dataBind($modelType, $modelName, $tableName, $sqlData, $mvc, $query_pass);
            foreach ($sqlData as $key => $value) {
                $$key = $value;
                @oci_bind_by_name($stid, ":$key", $$key, $sqlBind[$key]['DATA_SIZE'], $sqlBind[$key]['SQL_TYPE']);
                $sqlCommand = str_replace(":$key", "'$value'", $sqlCommand);
            }
        }

        // execute
        @oci_execute($stid, OCI_NO_AUTO_COMMIT);

        // result
        $error = oci_error($stid);
        if (!$error) {
            $dbRows['status'] = 'SUCCESS';
            switch ($action) {
                case 'SELECT':
                    $dbRows['result']['total'] = @oci_fetch_all($stid, $rows, $offset, $limit, OCI_FETCHSTATEMENT_BY_ROW);
                    empty($rows) ? $rows = null : null;
                    if (!is_null($keyName) && !is_null($rows)) {
                        $keyName = strtoupper($keyName);
                        foreach ($rows as $key => $value) {
                            $data[$value[$keyName]] = $value;
                        }
                    } else {
                        $data = $rows;
                    }
                    $dbRows['result']['data'] = $data;
                    break;
                case 'INSERT':
                case 'UPDATE':
                case 'DELETE':
                case 'MERGE':
                    $rows = @oci_num_rows($stid);
                    if (substr(trim($action), -1) === 'E') {
                        $todo = strtolower($action) . 'd';
                    } else {
                        $todo = strtolower($action) . 'ed';
                    }
                    $dbRows['result']['total'] = $rows;
                    $dbRows['result']['message'] = "$rows row(s) $todo.";
                    break;
                default:
                    die("【ERROR】Model is not support \"$action\".");
            }
        } else {
            $dbRows['status'] = 'ERROR';
            $dbRows['result'] = $error['message'];
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
                        $data_size = isset($value['DATA_SIZE']) ? empty($value['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $value['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                        switch ($data_size) {
                            case 'YYYY-MM-DD':
                                $data[$key] = date('Y-m-d');
                                break;
                            case 'YYYY-MM-DD HH24:MI:SS':
                                $data[$key] = date('Y-m-d H:i:s');
                                break;
                            default:
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
        // config
        if ($modelType === 'server') {
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->$tableName->schema;
        } else {
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->schema;
        }

        foreach ($sqlData as $key => $value) {
            if (isset($schema[$key])) {
                switch ($schema[$key]['DATA_TYPE']) {
                    case 'CHAR':
                    case 'NCHAR':
                        $data_size = $schema[$key]['DATA_SIZE'];
                        $sql_type = SQLT_AFC;
                        break;
                    case 'VARCHAR2':
                    case 'NVARCHAR2':
                        $data_size = $schema[$key]['DATA_SIZE'];
                        $sql_type = SQLT_CHR;
                        break;
                    case 'NUMBER':
                        $data_size = 22;
                        $sql_type = SQLT_CHR;
                        break;
                    case 'FLOAT':
                    case 'TIMESTAMP':
                    case 'TIMESTAMP WITH TIME ZONE':
                    case 'TIMESTAMP WITH LOCAL TIME ZONE':
                    case 'DATE':
                    case 'NCLOB':
                        $data_size = -1;
                        $sql_type = SQLT_CHR;
                        break;
                }
            } else {
                if ($query_pass) {
                    $data_size = null;
                    $sql_type = null;
                } else {
                    ErrorBase::triggerError("Column name \"$key\" can't find in model schema", 4, 0);
                }
            }

            $sqlBind[$key]['DATA_SIZE'] = $data_size;
            $sqlBind[$key]['SQL_TYPE'] = $sql_type;
        }
        return $sqlBind;
    }

}