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
use Ntch\Pocoapoco\WebRestful\Models\Database\DqlInterface;

class Dql extends PostgresBase implements DqlInterface
{

    /**
     * @inheritDoc
     */
    public static function select(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName, array $data, bool $distinct)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $table = self::$databaseObject[$mvc][$modelName]->tableName;
        $schema = self::$databaseObject[$mvc][$modelName]->schema;
        $permission = $schemaName;

        if (empty($data)) {
            $sql_search = '*';
        } else {
            $sql_search = '';
            foreach ($data as $key => $value) {
                $colName = null;
                $alias = null;
                if(is_int($key)) {
                    $colName = $value;
                    if (preg_match('/[a-zA-Z]+\(+\w+\)$/', $colName)) {
                        $sql_search .= "$colName, ";
                    } else {
                        if ($schema[$colName]['DATA_TYPE'] === 'DATE') {
                            $data_size = $schema[$colName]['DATA_SIZE'];
                            $sql_search .= "TO_CHAR($colName, '$data_size'), ";
                        } else {
                            $sql_search .= "$colName, ";
                        }
                    }
                } else {
                    $colName = $key;
                    $alias = $value;
                    if (preg_match('/[a-zA-Z]+\(+\w+\)$/', $colName)) {
                        $sql_search .= "$colName AS $alias, ";
                    } else {
                        if ($schema[$colName]['DATA_TYPE'] === 'DATE') {
                            $data_size = $schema[$colName]['DATA_SIZE'];
                            $sql_search .= "TO_CHAR($colName, '$data_size') AS $alias, ";
                        } else {
                            $sql_search .= "$colName AS $alias, ";
                        }
                    }
                }
            }
            $sql_search = substr(trim($sql_search), 0, -1);
        }

        if($distinct) {
            $sqlCommand = "\nSELECT DISTINCT \n$sql_search \nFROM $permission.$table ";
        } else {
            $sqlCommand = "\nSELECT $sql_search \nFROM $permission.$table ";
        }

        return $sqlCommand;
    }

    /**
     * @inheritDoc
     */
    public static function where(string $mvc, string $modelName, string $tableName, array $data, array $data_bind)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $schema = self::$databaseObject[$mvc][$modelName]->schema;

        $sql_where = '';
        $data_where = [];
        $data_flag = count($data_bind) + 1;
        foreach ($data as $colName => $value) {
            $sql_where .= "$colName ";
            if(is_array($value)) {
                if (is_null($value[0])) {
                    $sql_where .= "IS NOT NULL AND ";
                } else {
                    if ($schema[$colName]['DATA_TYPE'] === 'DATE') {
                        $data_size = $schema[$colName]['DATA_SIZE'];
                        $sql_where .= "TO_DATE($$data_flag, '$data_size'), ";
                    } else {
                        $sql_where .= "$value[1] $$data_flag AND ";
                    }
                    $data_where[$data_flag] = $colName;
                    $data_bind[$data_flag] = $value[0];
                }
            } else {
                if (is_null($value)) {
                    $sql_where .= "IS NULL AND ";
                } else {
                    if ($schema[$colName]['DATA_TYPE'] === 'DATE') {
                        $data_size = $schema[$colName]['DATA_SIZE'];
                        $sql_where .= "TO_DATE($$data_flag, '$data_size'), ";
                    } else {
                        $sql_where .= "= $$data_flag AND ";
                    }
                    $data_where[$data_flag] = $colName;
                    $data_bind[$data_flag] = $value;
                }
            }
            $data_flag++;
        }
        $sql_where = substr(trim($sql_where), 0, -4);

        $sqlCommand = "\nWHERE $sql_where";
        return $sql = ['command' => $sqlCommand, 'data' => $data_where, 'data_bind' => $data_bind];
    }

    /**
     * @inheritDoc
     */
    public static function orderby(array $data)
    {
        $sql_sort = '';
        foreach ($data as $key => $value) {
            $sql_sort .= "$value, ";
        }
        $sql_sort = substr(trim($sql_sort), 0, -1);

        $sqlCommand = "\nORDER BY $sql_sort";
        return $sqlCommand;
    }

    /**
     * @inheritDoc
     */
    public static function groupby(array $data)
    {
        $sql_sort = '';
        foreach ($data as $key => $value) {
            $sql_sort .= "$value, ";
        }
        $sql_sort = substr(trim($sql_sort), 0, -1);

        $sqlCommand = "\nGROUP BY $sql_sort";
        return $sqlCommand;
    }

}