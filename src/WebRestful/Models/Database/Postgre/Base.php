<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Postgre;

use Ntch\Pocoapoco\WebRestful\Models\Database\BaseInterface;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;
use SebastianBergmann\CodeCoverage\Report\PHP;

class Base extends ModelBase implements BaseInterface
{

    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        foreach (self::$databaseList['postgre']['server'] as $serverName => $serverConfig) {
            $this->checkDriverConfig($serverName, $serverConfig);
            $conn = $this->connect($serverConfig);

            if ($conn) {
                self::$databaseList['postgre']['server'][$serverName]['connect']['status'] = 'success';
                self::$databaseList['postgre']['server'][$serverName]['connect']['result'] = $conn;
            } else {
                self::$databaseList['postgre']['server'][$serverName]['connect']['status'] = 'error';
            }
        }
        isset(self::$databaseObject['postgre']->server) ? $this->loadModelUserSchema() : null;
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
        self::$databaseList['postgre']['server'][$serverName]['schema'] = (!isset($driver['schema']) || is_null($driver['schema'])) ? 'public' : $driver['schema'];

        foreach ($driverConfigList as $key) {
            isset($driver[$key]) ? null : die("【ERROR】Model $serverName tag \"$key\" is not exist.");
        }
    }

    /**
     * @inheritDoc
     */
    public function loadModelUserSchema()
    {
        foreach (self::$databaseObject['postgre']->server as $serverName => $serverInfo) {
            if (self::$databaseList['postgre']['server'][$serverName]['connect']['status'] === 'success') {

                $schema = self::$databaseList['postgre']['server'][$serverName]['schema'];
                $allTabColumns = $this->allTabColumns($serverName, $schema);
                if ($allTabColumns['status'] === 'SUCCESS') {
                    for ($i = 0; $i < $allTabColumns['result']['total']; $i++) {
                        $tableName = $allTabColumns['result']['data'][$i]['table_name'];
                        $columnName = $allTabColumns['result']['data'][$i]['column_name'];

                        isset(self::$databaseObject['postgre']->server[$serverName]->$tableName) ? null : self::$databaseObject['postgre']->server[$serverName]->$tableName = new \stdClass();
                        self::$databaseObject['postgre']->server[$serverName]->$tableName->schema[$columnName]['DATA_TYPE'] = $allTabColumns['result']['data'][$i]['data_type'];

                        self::$databaseObject['postgre']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = isset($res[1][0]) ? $res[1][0] : null;

                        self::$databaseObject['postgre']->server[$serverName]->$tableName->schema[$columnName]['NULLABLE'] = $allTabColumns['result']['data'][$i]['is_nullable'] === 'YES' ? 'Y' : 'N';
                        self::$databaseObject['postgre']->server[$serverName]->$tableName->schema[$columnName]['DATA_DEFAULT'] = $allTabColumns['result']['data'][$i]['column_default'];
                        self::$databaseObject['postgre']->server[$serverName]->$tableName->schema[$columnName]['KEY_TYPE'] = $allTabColumns['result']['data'][$i]['ordinal_position'];
                        self::$databaseObject['postgre']->server[$serverName]->$tableName->schema[$columnName]['COMMENT'] = $allTabColumns['result']['data'][$i]['description'];
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function allTabColumns(string $serverName, string $serachName)
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
        return self::query('server', $serverName, null, $sql, null, null, 0, -1);
    }

    /**
     * @inheritDoc
     */
    public static function query(string $modelType, string $modelName, ?string $tableName, string $sqlCommand, ?array $sqlData, ?string $keyName, int $offset, int $limit)
    {
        // config
        $modelType === 'server' ? $serverName = $modelName : $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
        $conn = self::$databaseList['postgre']['server'][$serverName]['connect']['result'];

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
        $isLegal = true;
        if (!is_null($sqlData)) {
            $schema = self::$databaseList['postgre']['server'][$serverName]['schema'];
            $tableName = "$schema.$tableName";
            foreach ($sqlData as $key => $value) {
                if ($isLegal) {
                    $covert = @pg_convert($conn, $tableName, [$key => $value]);
                }
                if (!$covert) {
                    $isLegal = false;
                }
                $sqlCommand = str_replace(":$key:", "'$value'", $sqlCommand);

            }
        }

        // result
        $error = pg_last_error($conn);
        if (!$error && $isLegal) {
            $dbRows['status'] = 'SUCCESS';
            switch ($action) {
                case 'SELECT':
                    $result = @pg_query($conn, $sqlCommand);
                    empty($result) ? $result = null : null;
                    $rows = pg_fetch_all($result);
                    if (!is_null($keyName) && !is_null($rows)) {
                        $keyName = strtolower($keyName);
                        foreach ($rows as $key => $value) {
                            $data[$value[$keyName]] = $value;
                            unset($data[$value[$keyName]][$keyName]);
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
                    @pg_query($conn, "BEGIN");
                    $result = @pg_query($conn, $sqlCommand);
                    $rows = @pg_affected_rows($result);
                    if (substr(trim($action), -1) === 'E') {
                        $todo = strtolower($action) . 'd';
                    } else {
                        $todo = strtolower($action) . 'ed';
                    }
                    $dbRows['result'] = "$rows row(s) $todo.";
                    break;
                default:
                    die("【ERROR】Model is not support \"$action\".");
            }
        } else {
            if (!$isLegal) {
                $error = 'Invalid Parameter';
            }
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
                            if ($value['DATA_TYPE'] == 'uuid') {
                                $data[$key] = self::uuid();
                            } else {
                                $data[$key] = self::sqlId();
                            }
                        }
                        break;
                    case 'UPDATE_DATE':
                        if(!strpos($value['DATA_SIZE'], '+') || !strpos($value['DATA_SIZE'], '-')) {
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
    public static function dataBind(string $modelType, string $modelName, string $tableName, array $sqlData)
    {

    }

}