<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Postgres;

use Ntch\Pocoapoco\WebRestful\Models\Database\BaseInterface;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;
use PHPUnit\Runner\Extension\PharLoader;

class Base extends ModelBase implements BaseInterface
{

    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * @inheritDoc
     */
    public function execute(string $mvc)
    {
        foreach (self::$databaseList[$mvc]['postgres']['server'] as $serverName => $serverConfig) {
            $this->checkDriverConfig($serverName, $serverConfig);
            $conn = $this->connect($serverConfig);

            if ($conn) {
                self::$databaseList[$mvc]['postgres']['server'][$serverName]['connect']['status'] = 'success';
                self::$databaseList[$mvc]['postgres']['server'][$serverName]['connect']['result'] = $conn;
            } else {
                self::$databaseList[$mvc]['postgres']['server'][$serverName]['connect']['status'] = 'error';
            }
        }
        isset(self::$databaseObject[$mvc]['postgres']->server) ? $this->loadModelUserSchema($mvc) : null;
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
        $conn = @pg_pconnect("host=$ip port=$port dbname=$database user=$user password=$password");

        return $conn;
    }

    /**
     * @inheritDoc
     */
    public function checkDriverConfig(string $serverName, array $driver)
    {
        $driverConfigList = ['ip', 'port', 'database', 'user', 'password'];
        self::$databaseList['postgres']['server'][$serverName]['schema'] = (!isset($driver['schema']) || is_null($driver['schema'])) ? 'public' : $driver['schema'];

        foreach ($driverConfigList as $key) {
            isset($driver[$key]) ? null : die("【ERROR】Model $serverName tag \"$key\" is not exist.");
        }
    }

    /**
     * @inheritDoc
     */
    public function loadModelUserSchema(string $mvc)
    {
        foreach (self::$databaseObject[$mvc]['postgres']->server as $serverName => $serverInfo) {
            if (self::$databaseList[$mvc]['postgres']['server'][$serverName]['connect']['status'] === 'success') {

                $schema = self::$databaseList[$mvc]['postgres']['server'][$serverName]['schema'];
                $allTabColumns = $this->allTabColumns($serverName, $schema, $mvc);
                if ($allTabColumns['status'] === 'SUCCESS') {
                    for ($i = 0; $i < $allTabColumns['result']['total']; $i++) {
                        $tableName = $allTabColumns['result']['data'][$i]['table_name'];
                        $columnName = $allTabColumns['result']['data'][$i]['column_name'];

                        isset(self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName) ? null : self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName = new \stdClass();
                        self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName->schema[$columnName]['DATA_TYPE'] = $allTabColumns['result']['data'][$i]['data_type'];

                        self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = isset($res[1][0]) ? $res[1][0] : null;

                        self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName->schema[$columnName]['NULLABLE'] = $allTabColumns['result']['data'][$i]['is_nullable'] === 'YES' ? 'Y' : 'N';
                        self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName->schema[$columnName]['DATA_DEFAULT'] = $allTabColumns['result']['data'][$i]['column_default'];
                        self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName->schema[$columnName]['KEY_TYPE'] = $allTabColumns['result']['data'][$i]['ordinal_position'];
                        self::$databaseObject[$mvc]['postgres']->server[$serverName]->$tableName->schema[$columnName]['COMMENT'] = $allTabColumns['result']['data'][$i]['description'];
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
                isc.table_name, isc.column_name, isc.ordinal_position, isc.column_default, isc.is_nullable, isc.data_type, isc.character_maximum_length, isc.datetime_precision, isc.numeric_scale, pcpd.description, 
                CASE iskcu.ordinal_position
                    WHEN 1 THEN 'P'
                END AS ordinal_position
            FROM
                pg_catalog.pg_statio_all_tables AS pcpsat
            RIGHT JOIN pg_catalog.pg_description pcpd ON (pcpd.objoid = pcpsat.relid)
            RIGHT JOIN information_schema.columns isc ON (pcpd.objsubid = isc.ordinal_position
                AND isc.table_schema = pcpsat.schemaname AND isc.table_name = pcpsat.relname)
            LEFT JOIN information_schema.key_column_usage iskcu ON (iskcu.table_schema = isc.table_schema AND iskcu.table_name = isc.table_name AND iskcu.column_name = isc.column_name)
            WHERE
                isc.table_schema = '$serachName'
        sqlCommand;
        return self::query('server', $serverName, null, $sql, null, [], null, 0, -1, $mvc, false);
    }

    /**
     * @inheritDoc
     */
    public static function query(string $modelType, string $modelName, ?string $tableName, string $sqlCommand, ?array $sqlData, array $sqlData_bind, ?string $keyName, int $offset, int $limit, string $mvc, bool $query_pass)
    {
        // config
        $modelType === 'server' ? $serverName = $modelName : $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
        $conn = self::$databaseList[$mvc]['postgres']['server'][$serverName]['connect']['result'];

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

        // result
        $error = pg_last_error($conn);
        if (!$error) {
            $dbRows['status'] = 'SUCCESS';
            switch ($action) {
                case 'SELECT':
                    if (!is_null($sqlData)) {
                        $result = pg_query_params($conn, $sqlCommand, $sqlData_bind);
                        foreach ($sqlData_bind as $data_flag => $value) {
                            $sqlCommand = str_replace("$$data_flag", "'$value'", $sqlCommand);
                        }
                    } else {
                        $result = @pg_query($conn, $sqlCommand);
                    }
                    if(!$result){
                        $dbRows['status'] = 'ERROR';
                        $dbRows['result'] = 'SELECT Error';
                        break;
                    }
                    // parse result
                    empty($result) ? $result = null : null;
                    $rows = pg_fetch_all($result);
                    $data = [];
                    if (!is_null($keyName) && !is_null($rows)) {
                        $keyName = strtolower($keyName);
                        foreach ($rows as $key => $value) {
                            $data[$value[$keyName]] = $value;
                        }
                    } else {
                        $data = $rows;
                    }

                    $dbRows['result']['total'] = @pg_affected_rows($result);
                    $dbRows['result']['data'] = @$data;
                    break;
                case 'INSERT':
                case 'UPDATE':
                case 'DELETE':
                case 'MERGE':
                    if (!is_null($sqlData)) {
                        $result = pg_query_params($conn, $sqlCommand, $sqlData_bind);
                        foreach ($sqlData_bind as $data_flag => $value) {
                            $sqlCommand = str_replace("$$data_flag", "'$value'", $sqlCommand);
                        }
                    } else {
                        @pg_query($conn, 'BEGIN');
                        $result = @pg_query($conn, $sqlCommand);
                    }
                    if(!$result){
                        $dbRows['status'] = 'ERROR';
                        $dbRows['result'] = 'DML Error';
                        break;
                    }
                    // parse result
                    $rows = @pg_affected_rows($result);
                    if (substr(trim($action), -1) === 'E') {
                        $todo = strtolower($action) . 'd';
                    } else {
                        $todo = strtolower($action) . 'ed';
                    }
                    $dbRows['result']['total'] = $rows;
                    $dbRows['result']['message'] = "$rows row(s) $todo.";
                    break;
                case 'CREATE':
                    if (!is_null($sqlData)) {
                        $result = pg_query_params($conn, $sqlCommand, $sqlData_bind);
                        foreach ($sqlData_bind as $data_flag => $value) {
                            $sqlCommand = str_replace("$$data_flag", "'$value'", $sqlCommand);
                        }                    } else {
                        @pg_query($conn, $sqlCommand);
                    }
                    unset($dbRows['result']);
                    break;
                default:
                    die("【ERROR】Model is not support \"$action\".");
            }
        } else {
            $dbRows['status'] = 'ERROR';
            $dbRows['result'] = $error;
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
                            if (empty($data[$key])) {
                                if ($value['DATA_TYPE'] == 'uuid') {
                                    $data[$key] = self::uuid();
                                } else {
                                    $data[$key] = self::sqlId();
                                }
                            }
                        }
                        break;
                    case 'UPDATE_DATE':
                        if (!strpos($value['DATA_SIZE'], '+') || !strpos($value['DATA_SIZE'], '-')) {
                            $data_size = $value['DATA_SIZE'];
                        } else {
                            $data_size = 'yyyy-mm-dd hh24:mi:ss';
                        }
                        switch ($data_size) {
                            case 'yyyy-mm-dd':
                                $data[$key] = date('Y-m-d');
                                break;
                            case 'yyyy-mm-dd hh24:mi:ss':
                                $zone = isset(explode('yyyy-mm-dd hh24:mi:ss', $value['DATA_SIZE'])[1]) ? explode('yyyy-mm-dd hh24:mi:ss', $value['DATA_SIZE'])[1] : null;
                                $data[$key] = (string)date('Y-m-d H:i:s') . $zone;
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

    }

}