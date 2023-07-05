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
    public static function insert(string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgres']['table'][$modelName]['table'];
        }
        $permission = self::$databaseList[$mvc]['postgres']['server'][$serverName]['schema'];
        $user = self::$databaseList[$mvc]['postgres']['server'][$serverName]['user'];

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
        foreach ($data as $key => $value) {
            // data_bind
            empty($data_bind) ? $data_bind[0] = null : null;
            in_array($key, $data_bind) ? null : array_push($data_bind, $key);
            $data_flag = array_search($key, $data_bind);

            $sql_key .= "$key, ";
            if (@$schema[$key]['DATA_TYPE'] === 'DATE') {
                $data_size = $schema[$key]['DATA_SIZE'];
                $sql_value .= "TO_DATE($$data_flag, '$data_size'), ";
            } else {
                $sql_value .= "$$data_flag, ";
            }
        }
        $sql_key = substr(trim($sql_key), 0, -1);
        $sql_value = substr(trim($sql_value), 0, -1);
        $sql_key .= ')';
        $sql_value .= ')';

        $sqlCommand = "$sql_key \nVALUES $sql_value\n";
        return $sql = ['command' => $sqlCommand, 'data' => $data, 'data_bind' => $data_bind];
    }

    /**
     * @inheritDoc
     */
    public static function delete(string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgres']['table'][$modelName]['table'];
        }
        $user = self::$databaseList[$mvc]['postgres']['server'][$serverName]['user'];

        $sql = "\nDELETE FROM $table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function update(string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
        } else {
            $serverName = self::$databaseList[$mvc]['postgres']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgres']['table'][$modelName]['table'];
        }
        $permission = self::$databaseList[$mvc]['postgres']['server'][$serverName]['schema'];
        $user = self::$databaseList[$mvc]['postgres']['server'][$serverName]['user'];

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
        foreach ($data as $key => $value) {
            // data_bind
            empty($data_bind) ? $data_bind[0] = null : null;
            in_array($key, $data_bind) ? null : array_push($data_bind, $key);
            $data_flag = array_search($key, $data_bind);

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
        return $sql = ['command' => $sqlCommand, 'data' => $data, 'data_bind' => $data_bind];
    }

}