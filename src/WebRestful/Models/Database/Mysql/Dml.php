<?php


namespace Ntch\Pocoapoco\WebRestful\Models\Database\Mysql;

use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Base as MysqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DmlInterface;

class Dml extends MysqlBase implements DmlInterface
{

    /**
     * @inheritDoc
     */
    public static function insert(string $modelType, string $modelName, string $tableName)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList['mysql']['table'][$modelName]['server'];
            $table = self::$databaseList['mysql']['table'][$modelName]['table'];
        }
        $user = self::$databaseList['mysql']['server'][$serverName]['user'];

        $sql = "\nINSERT INTO `$user`.`$table` ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function value(string $modelType, string $modelName, string $tableName, array $data)
    {
        $serverName = self::$databaseList['mysql']['table'][$modelName]['server'];
        $schema = self::$databaseObject['mysql']->table[$modelName]->schema;

        $data = MysqlBase::systemSet('INSERT', $schema, $data);

        $sql_key = '(';
        $sql_value = '(';
        foreach ($data as $key => $value) {
            $sql_key .= "`$key`, ";
            if (@$schema[$key]['DATA_TYPE'] === 'DATE') {
                $data_size = $schema[$key]['DATA_SIZE'];
                $sql_value .= "TO_DATE(?, '$data_size'), ";
            } else {
                $sql_value .= "?, ";
            }
        }
        $sql_key = substr(trim($sql_key), 0, -1);
        $sql_value = substr(trim($sql_value), 0, -1);
        $sql_key .= ')';
        $sql_value .= ')';

        $sqlCommand = "$sql_key \nVALUES $sql_value\n";
        return $sql = ['command' => $sqlCommand, 'data' => $data];
    }

    /**
     * @inheritDoc
     */
    public static function delete(string $modelType, string $modelName, string $tableName)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList['mysql']['table'][$modelName]['server'];
            $table = self::$databaseList['mysql']['table'][$modelName]['table'];
        }
        $user = self::$databaseList['mysql']['server'][$serverName]['user'];

        $sql = "\nDELETE FROM `$user`.`$table` ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function update(string $modelType, string $modelName, string $tableName)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList['mysql']['table'][$modelName]['server'];
            $table = self::$databaseList['mysql']['table'][$modelName]['table'];
        }
        $user = self::$databaseList['mysql']['server'][$serverName]['user'];

        $sql = "\nUPDATE `$user`.`$table` ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function set(string $modelType, string $modelName, string $tableName, array $data)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $schema = self::$databaseObject['mysql']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList['mysql']['table'][$modelName]['server'];
            $schema = self::$databaseObject['mysql']->$modelType[$modelName]->schema;
        }

        $data = MysqlBase::systemSet('UPDATE', $schema, $data);

        $sql_set = '';
        foreach ($data as $key => $value) {
            $sql_set .= "`$key` = ";
            if (is_null($value)) {
                $sql_set .= "?, ";
            } else {
                if ($schema[$key]['DATA_TYPE'] === 'DATE') {
                    $data_size = $schema[$key]['DATA_SIZE'];
                    $sql_set .= "TO_DATE(?, '$data_size'), ";
                } else {
                    $sql_set .= "?, ";
                }
            }
        }
        $sql_set = substr(trim($sql_set), 0, -1);

        $sqlCommand = "\nSET $sql_set";
        return $sql = ['command' => $sqlCommand, 'data' => $data];
    }

}