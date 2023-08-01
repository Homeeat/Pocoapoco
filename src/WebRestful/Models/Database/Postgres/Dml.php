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

use Ntch\Pocoapoco\WebRestful\Models\Database\Postgres\Base as PostgresBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DmlInterface;

class Dml extends PostgresBase implements DmlInterface
{

    /**
     * @inheritDoc
     */
    public static function insert(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgres']['table'][$modelName]['table'];
        }
        $permission = $schemaName;
        $user = $userName;

        $sql = "\nINSERT INTO $permission.$table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function values(string $modelType, string $modelName, string $tableName, array $data, array $data_bind, string $mvc)
    {
        $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
        $schema = self::$databaseObject[$mvc]['postgres']->table[$modelName]->schema;

        $data = PostgresBase::systemSet('INSERT', $schema, $data);

        $sql_key = '(';
        $sql_value = '(';
        $data_key = array();
        $data_flag = count($data_bind) + 1;
        foreach ($data as $key => $value) {
            $data_key[$data_flag] = $key;
            $data_bind[$data_flag] = $value;
            $sql_key .= "$key, ";
            if (@$schema[$key]['DATA_TYPE'] === 'DATE') {
                $data_size = $schema[$key]['DATA_SIZE'];
                $sql_value .= "TO_DATE($$data_flag, '$data_size'), ";
            } else {
                $sql_value .= "$$data_flag, ";
            }
            $data_flag++;
        }
        $sql_key = substr(trim($sql_key), 0, -1);
        $sql_value = substr(trim($sql_value), 0, -1);
        $sql_key .= ')';
        $sql_value .= ')';

        $sqlCommand = "$sql_key \nVALUES $sql_value\n";
        return $sql = ['command' => $sqlCommand, 'data' => $data_key, 'data_bind' => $data_bind];
    }

    /**
     * @inheritDoc
     */
    public static function delete(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgres']['table'][$modelName]['table'];
        }
        $permission = $schemaName;
        $user = $userName;

        $sql = "\nDELETE FROM $permission.$table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function update(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgres']['table'][$modelName]['table'];
        }
        $permission = $schemaName;
        $user = $userName;

        $sql = "\nUPDATE $permission.$table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function set(string $modelType, string $modelName, string $tableName, array $data, array $data_bind, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $schema = self::$databaseObject[$mvc]['postgres']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
            $schema = self::$databaseObject[$mvc]['postgres']->$modelType[$modelName]->schema;
        }

        $data = PostgresBase::systemSet('UPDATE', $schema, $data);

        $sql_set = '';
        $data_flag = count($data_bind) + 1;
        $data_key = array();
        foreach ($data as $key => $value) {
            $data_key[$data_flag] = $key;
            $data_bind[$data_flag] = $value;

            $sql_set .= "$key = ";
            if (is_null($value)) {
                $sql_set .= "$$data_flag, ";
            } else {
                if ($schema[$key]['DATA_TYPE'] === 'DATE') {
                    $data_size = $schema[$key]['DATA_SIZE'];
                    $sql_set .= "TO_DATE($$data_flag, '$data_size'), ";
                } else {
                    $sql_set .= "$$data_flag, ";
                }
            }
            $data_flag++;
        }

        $sql_set = substr(trim($sql_set), 0, -1);

        $sqlCommand = "\nSET $sql_set";
        return $sql = ['command' => $sqlCommand, 'data' => $data_key, 'data_bind' => $data_bind];
    }

}