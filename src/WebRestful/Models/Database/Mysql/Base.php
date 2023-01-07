<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Mysql;

use Ntch\Pocoapoco\Error\Base as ErrorBase;
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
        foreach (self::$databaseList[$mvc]['mysql']['server'] as $serverName => $serverConfig) {
            $this->checkDriverConfig($serverName, $serverConfig, $mvc);
            $conn = $this->connect($serverConfig);

            if ($conn) {
                self::$databaseList[$mvc]['mysql']['server'][$serverName]['connect']['status'] = 'success';
                self::$databaseList[$mvc]['mysql']['server'][$serverName]['connect']['result'] = $conn;
            } else {
                $error = mysqli_connect_error();
                self::$databaseList[$mvc]['mysql']['server'][$serverName]['connect']['status'] = 'error';
                self::$databaseList[$mvc]['mysql']['server'][$serverName]['connect']['result'] = $error;
            }
        }
        isset(self::$databaseObject[$mvc]['mysql']->server) ? $this->loadModelUserSchema($mvc) : null;
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
        $tns = "$ip:$port";
        $conn = @mysqli_connect($tns, $user, $password, $database);

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
        foreach (self::$databaseObject[$mvc]['mysql']->server as $serverName => $serverInfo) {
            if (self::$databaseList[$mvc]['mysql']['server'][$serverName]['connect']['status'] === 'success') {

                $allTabColumns = $this->allTabColumns($serverName, self::$databaseList[$mvc]['mysql']['server'][$serverName]['database'], $mvc);
                if ($allTabColumns['status'] === 'SUCCESS') {
                    for ($i = 0; $i < $allTabColumns['result']['total']; $i++) {
                        $tableName = $allTabColumns['result']['data'][$i]['TABLE_NAME'];
                        $columnName = $allTabColumns['result']['data'][$i]['COLUMN_NAME'];

                        isset(self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName) ? null : self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName = new \stdClass();
                        self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName->schema[$columnName]['DATA_TYPE'] = $allTabColumns['result']['data'][$i]['DATA_TYPE'];

                        preg_match_all('/(?:\()(.*)(?:\))/i', $allTabColumns['result']['data'][$i]['COLUMN_TYPE'], $res);
                        self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName->schema[$columnName]['DATA_SIZE'] = isset($res[1][0]) ? $res[1][0] : null;

                        self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName->schema[$columnName]['NULLABLE'] = $allTabColumns['result']['data'][$i]['IS_NULLABLE'] === 'YES' ? 'Y' : 'N';
                        self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName->schema[$columnName]['DATA_DEFAULT'] = $allTabColumns['result']['data'][$i]['COLUMN_DEFAULT'];
                        switch ($allTabColumns['result']['data'][$i]['COLUMN_KEY']) {
                            case 'PRI':
                                self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName->schema[$columnName]['KEY_TYPE'] = 'P';
                                break;
                        }
                        self::$databaseObject[$mvc]['mysql']->server[$serverName]->$tableName->schema[$columnName]['COMMENT'] = $allTabColumns['result']['data'][$i]['COLUMN_COMMENT'];
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
            SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_TYPE, COLUMN_KEY, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$serachName' ORDER BY ORDINAL_POSITION
        sqlCommand;
        return self::query('server', $serverName, null, $sql, null, [], null, 0, -1, $mvc, false);
    }

    /**
     * @inheritDoc
     */
    public static function query(string $modelType, string $modelName, ?string $tableName, string $sqlCommand, ?array $sqlData, array $sqlData_bind, ?string $keyName, int $offset, int $limit, string $mvc, bool $query_pass)
    {
        // config
        $modelType === 'server' ? $serverName = $modelName : $serverName = self::$databaseList[$mvc]['mysql']['table'][$modelName]['server'];
        $conn = self::$databaseList[$mvc]['mysql']['server'][$serverName]['connect']['result'];

        // auto commit
        mysqli_autocommit($conn, false);

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
        $stmt = mysqli_prepare($conn, $sqlCommand);

        // avoid sql injection
        empty($sqlData) ? $sqlData = null : null;
        if (!is_null($sqlData)) {

            $sqlBind = self::dataBind($modelType, $modelName, $tableName, $sqlData, $mvc, $query_pass);
            $sql_type = '';
            $sql_data = [];
            foreach ($sqlData as $key => $value) {
                $sql_type .= $sqlBind[$key]['SQL_TYPE'];
                $$key = $value;
                $sql_data[] = &$$key;

                $sqlCommand = preg_replace('/\?/', "'$value'", $sqlCommand, 1);
            }

            if (count($sqlData) > 1) {
                @array_unshift($sql_data, $sql_type);
                @call_user_func_array([$stmt, 'bind_param'], $sql_data);
            } else {
                @mysqli_stmt_bind_param($stmt, $sql_type, $sql_data[0]);
            }

            $error = mysqli_stmt_error($stmt);
        } else {
            $error = mysqli_error($conn);
        }

        // result
        if (!$error) {
            $dbRows['status'] = 'SUCCESS';

            switch ($action) {
                case 'SELECT':
                    $dbRows['result']['total'] = 0;

                    if (!is_null($sqlData)) {
                        @mysqli_stmt_execute($stmt);
                        $result = @mysqli_stmt_get_result($stmt);
                    } else {
                        $result = mysqli_query($conn, $sqlCommand);
                    }

                    if (is_null($keyName)) {
                        while ($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                            $dbRows['result']['data'][$dbRows['result']['total']] = $row;
                            $dbRows['result']['total']++;
                        }
                    } else {
                        $keyName = strtolower($keyName);
                        while ($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                            $dbRows['result']['data'][$row[$keyName]] = $row;
                            $dbRows['result']['total']++;
                        }
                    }
                    break;
                case 'INSERT':
                case 'UPDATE':
                case 'DELETE':
                    @mysqli_stmt_execute($stmt);

                    if (substr(trim($action), -1) === 'E') {
                        $todo = strtolower($action) . 'd';
                    } else {
                        $todo = strtolower($action) . 'ed';
                    }
                    $rows = @mysqli_stmt_affected_rows($stmt);
                    if ($rows < 0) {
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
                            case '%Y-%m-%d':
                                $data[$key] = date('Y-m-d');
                                break;
                            case '%Y-%m-%d %H:%i:%s':
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
        // config
        if ($modelType === 'server') {
            $schema = self::$databaseObject[$mvc]['mysql']->$modelType[$modelName]->$tableName->schema;
        } else {
            $schema = self::$databaseObject[$mvc]['mysql']->$modelType[$modelName]->schema;
        }

        foreach ($sqlData as $key => $value) {
            if (isset($schema[$key])) {
                switch (@$schema[$key]['DATA_TYPE']) {
                    case 'char':
                    case 'varchar':
                    case 'timestamp':
                    case 'datetime':
                    case 'date':
                    case 'time':
                    case 'year':
                        $sql_type = 's';
                        break;
                    case 'tinyint':
                    case 'smallint':
                    case 'mediumint':
                    case 'bigint':
                    case 'int':
                        $sql_type = 'i';
                        break;
                    case 'float':
                    case 'decimal':
                        $sql_type = 'd';
                        break;
                }
            } else {
                if ($query_pass) {
                    $sql_type = 's';
                } else {
                    ErrorBase::triggerError("Column name \"$key\" can't find in model schema", 4, 0);
                }
            }
            @$sqlBind[$key]['SQL_TYPE'] = $sql_type;
        }
        return $sqlBind;
    }

}