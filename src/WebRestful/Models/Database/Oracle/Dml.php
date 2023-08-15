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

use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Base as OracleBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DmlInterface;

class Dml extends OracleBase implements DmlInterface
{

    /**
     * @inheritDoc
     */
    public static function insert(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $table = self::$databaseObject[$mvc][$modelName]->tableName;
        $user = $userName;

        $sql = "\nINSERT INTO $user.$table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function values(string $mvc, string $modelName, string $tableName, array $data, array $data_bind)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $schema = self::$databaseObject[$mvc][$modelName]->schema;

        $data = OracleBase::systemSet('INSERT', $schema, $data);

        $sql_key = '(';
        $sql_value = '(';
        $data_key = array();
        $data_flag = count($data_bind);
        foreach ($data as $key => $value) {
            $data_key[$data_flag] = $key;
            $data_bind[$data_flag] = $value;

            $sql_key .= "$key, ";
            if ($schema[$key]['DATA_TYPE'] === 'DATE' || $schema[$key]['DATA_TYPE'] === 'TIMESTAMP') {
                $data_size = isset($schema[$key]['DATA_SIZE']) ? empty($schema[$key]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$key]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                $sql_value .= "TO_DATE(:$key$data_flag, '$data_size'), ";
            } elseif ($schema[$key]['DATA_TYPE'] === 'TIMESTAMP WITH TIME ZONE' || $schema[$key]['DATA_TYPE'] === 'TIMESTAMP WITH LOCAL TIME ZONE') {
                $data_size = isset($schema[$key]['DATA_SIZE']) ? empty($schema[$key]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$key]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                $sql_value .= "TO_TIMESTAMP(:$key$data_flag, '$data_size'), ";
            } else {
                $sql_value .= ":$key$data_flag, ";
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
    public static function delete(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $table = self::$databaseObject[$mvc][$modelName]->tableName;
        $user = $userName;

        $sql = "\nDELETE FROM $user.$table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function update(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $table = self::$databaseObject[$mvc][$modelName]->tableName;
        $user = $userName;

        $sql = "\nUPDATE $user.$table ";
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public static function set(string $mvc, string $modelName, string $tableName, array $data, array $data_bind)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $schema = self::$databaseObject[$mvc][$modelName]->schema;

        $data = OracleBase::systemSet('UPDATE', $schema, $data);

        $sql_set = '';
        $data_key = array();
        $data_flag = count($data_bind);
        foreach ($data as $key => $value) {
            $data_key[$data_flag] = $key;
            $data_bind[$data_flag] = $value;

            $sql_set .= "$key = ";
            if (is_null($value)) {
                $sql_set .= " null, ";
            } else {
                if ($schema[$key]['DATA_TYPE'] === 'DATE') {
                    $data_size = isset($schema[$key]['DATA_SIZE']) ? empty($schema[$key]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$key]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                    $sql_set .= "TO_DATE(:$key$data_flag, '$data_size'), ";
                } elseif ($schema[$key]['DATA_TYPE'] === 'TIMESTAMP WITH TIME ZONE' || $schema[$key]['DATA_TYPE'] === 'TIMESTAMP WITH LOCAL TIME ZONE') {
                    $data_size = isset($schema[$key]['DATA_SIZE']) ? empty($schema[$key]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$key]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                    $sql_set .= "TO_TIMESTAMP(:$key$data_flag, '$data_size'), ";
                } else {
                    $sql_set .= ":$key$data_flag, ";
                }
            }
            $data_flag++;
        }
        $sql_set = substr(trim($sql_set), 0, -1);

        $sqlCommand = "\nSET $sql_set";
        return $sql = ['command' => $sqlCommand, 'data' => $data_key, 'data_bind' => $data_bind];
    }

    /**
     * Merge table.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function merge(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $table = self::$databaseObject[$mvc][$modelName]->tableName;
        $user = $userName;

        $sql = "MERGE INTO $user.$table target ";
        return $sql;
    }

    /**
     * Merge using.
     *
     * @param string $user
     * @param string $tableName
     *
     * @return string
     */
    public static function using(string $user, string $tableName)
    {
        $sql = "\nUSING $user.$tableName source ";
        return $sql;
    }

    /**
     * Merge on.
     *
     * @param string $target
     * @param string $source
     *
     * @return string
     */
    public static function on(string $target, string $source)
    {
        $sql = "\nON ( target.$target = source.$source ) ";
        return $sql;
    }

    /**
     * Merge matched.
     *
     * @return string
     */
    public static function matched()
    {
        $sql = "\nWHEN MATCHED THEN ";
        return $sql;
    }

    /**
     * Merge update.
     *
     * @return string
     */
    public static function mergeUpdate()
    {
        $sql = "\nUPDATE SET ";
        return $sql;
    }

    /**
     * Merge update set key.
     *
     * @param string $modelName
     * @param array $colName
     *
     * @return string
     */
    public static function mergeSet(string $mvc, string $modelName, array $colName)
    {
        $sql_col = '';
        if (empty($colName)) {
            $schema = self::$databaseObject[$mvc][$modelName]->schema;

            foreach ($schema as $key => $value) {
                if ($value['KEY_TYPE'] !== 'P') {
                    $sql_col .= "\ntarget.$key = source.$key, ";
                }
            }
        } else {
            foreach ($colName as $key => $value) {
                $sql_col .= "\ntarget.$key = source.$key, ";
            }
        }
        $sql_col = substr(trim($sql_col), 0, -1);

        return "\n$sql_col";
    }

    /**
     * Merge not matched.
     *
     * @return string
     */
    public static function not()
    {
        $sql = "\nWHEN NOT MATCHED THEN ";
        return $sql;
    }

    /**
     * Merge insert.
     *
     * @return string
     */
    public static function mergeInsert()
    {
        $sql = "\nINSERT ";
        return $sql;
    }

    /**
     * Merge insert value.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string $tableName
     * @param array $colName
     *
     * @return string
     */
    public static function mergeValues(string $mvc, string $modelName, string $tableName, array $colName)
    {
        $sql_key = '(';
        $sql_value = '(';
        if (empty($colName)) {
            self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

            // config
            $schema = self::$databaseObject[$mvc][$modelName]->schema;
            $colName = $schema;
        }
        foreach ($colName as $key => $value) {
            $sql_key .= "target.$key, ";
            $sql_value .= "source.$key, ";
        }
        $sql_key = substr(trim($sql_key), 0, -1);
        $sql_value = substr(trim($sql_value), 0, -1);
        $sql_key .= ')';
        $sql_value .= ')';

        $sqlCommand = "$sql_key\nVALUES $sql_value\n";

        return $sqlCommand;
    }

}