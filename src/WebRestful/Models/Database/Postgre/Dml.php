<?php


namespace Ntch\Pocoapoco\WebRestful\Models\Database\Postgre;

use Ntch\Pocoapoco\WebRestful\Models\Database\Postgre\Base as PostgreBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DmlInterface;

class Dml extends PostgreBase implements DmlInterface
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
            $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
            $table = self::$databaseList['postgre']['table'][$modelName]['table'];
        }
        $permission = self::$databaseList['postgre']['server'][$serverName]['schema'];
        $user = self::$databaseList['postgre']['server'][$serverName]['user'];

        $sql = "\nINSERT INTO $permission.$table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function value(string $modelType, string $modelName, string $tableName, array $data)
    {
        $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
        $schema = self::$databaseObject['postgre']->table[$modelName]->schema;

        $data = PostgreBase::systemSet('INSERT', $schema, $data);

        $sql_key = '(';
        $sql_value = '(';
        foreach ($data as $key => $value) {
            $sql_key .= "$key, ";
            if (@$schema[$key]['DATA_TYPE'] === 'DATE') {
                $data_size = $schema[$key]['DATA_SIZE'];
                $sql_value .= "TO_DATE(:$key, '$data_size'), ";
            } else {
                $sql_value .= ":$key, ";
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
            $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
            $table = self::$databaseList['postgre']['table'][$modelName]['table'];
        }
        $user = self::$databaseList['postgre']['server'][$serverName]['user'];

        $sql = "\nDELETE FROM $table ";
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
            $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
            $table = self::$databaseList['postgre']['table'][$modelName]['table'];
        }
        $permission = self::$databaseList['postgre']['server'][$serverName]['schema'];
        $user = self::$databaseList['postgre']['server'][$serverName]['user'];

        $sql = "\nUPDATE $permission.$table ";
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
            $schema = self::$databaseObject['postgre']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
            $schema = self::$databaseObject['postgre']->$modelType[$modelName]->schema;
        }

        $data = PostgreBase::systemSet('UPDATE', $schema, $data);

        $sql_set = '';
        foreach ($data as $key => $value) {
            $sql_set .= "$key = ";
            if (is_null($value)) {
                $sql_set .= ":$key, ";
            } else {
                if ($schema[$key]['DATA_TYPE'] === 'DATE') {
                    $data_size = $schema[$key]['DATA_SIZE'];
                    $sql_set .= "TO_DATE(:$key, '$data_size'), ";
                } else {
                    $sql_set .= ":$key, ";
                }
            }
        }
        $sql_set = substr(trim($sql_set), 0, -1);

        $sqlCommand = "\nSET $sql_set";
        return $sql = ['command' => $sqlCommand, 'data' => $data];
    }

}