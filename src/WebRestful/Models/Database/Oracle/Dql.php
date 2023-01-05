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
use Ntch\Pocoapoco\WebRestful\Models\Database\DqlInterface;

class Dql extends OracleBase implements DqlInterface
{

    /**
     * @inheritDoc
     */
    public static function select(string $modelType, string $modelName, string $tableName, array $data, bool $distinct, string $mvc)
    {
        // config
        if ($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList[$mvc]['oracle']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['oracle']['table'][$modelName]['table'];
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->schema;
        }
        $user = self::$databaseList[$mvc]['oracle']['server'][$serverName]['user'];

        if (empty($data)) {
            $sql_search = '*';
        } else {
            $sql_search = '';
            foreach ($data as $key => $value) {
                $colName = null;
                $alias = null;
                if (is_int($key)) {
                    $colName = $value;
                    if (preg_match('/[a-zA-Z]+\(+\w+\)$/', $colName) || !strpos(' ', $colName)) {
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
                    if (preg_match('/[a-zA-Z]+\(+\w+\)$/', $colName) || !strpos(' ', $colName)) {
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

        if ($distinct) {
            $sqlCommand = "\nSELECT DISTINCT \n$sql_search \nFROM $user.$table ";
        } else {
            $sqlCommand = "\nSELECT $sql_search \nFROM $user.$table ";
        }

        return $sqlCommand;
    }

    /**
     * @inheritDoc
     */
    public static function where(string $modelType, string $modelName, string $tableName, array $data, array $data_bind, string $mvc)
    {
        // config
        if ($modelType === 'server') {
            $serverName = $modelName;
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList[$mvc]['oracle']['table'][$modelName]['server'];
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->schema;
        }

        $sql_where = '';
        $data_where = [];
        foreach ($data as $colName => $value) {
            $sql_where .= "$colName ";
            if (is_array($value)) {
                if (is_null($value[0])) {
                    $sql_where .= "IS NOT NULL AND ";
                } else {
                    if ($schema[$colName]['DATA_TYPE'] === 'DATE') {
                        $data_size = isset($schema[$colName]['DATA_SIZE']) ? empty($schema[$colName]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$colName]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                        $sql_where .= "= TO_DATE(:$colName, '$data_size') AND ";
                    } elseif ($schema[$colName]['DATA_TYPE'] === 'TIMESTAMP WITH TIME ZONE' || $schema[$colName]['DATA_TYPE'] === 'TIMESTAMP WITH LOCAL TIME ZONE') {
                        $data_size = isset($schema[$colName]['DATA_SIZE']) ? empty($schema[$colName]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$colName]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                        $sql_where .= "= TO_TIMESTAMP(:$colName, '$data_size') AND ";
                    } else {
                        $sql_where .= "$value[1] :$colName AND ";
                    }
                    $data_where[$colName] = $value[0];
                }
            } else {
                if (is_null($value)) {
                    $sql_where .= "IS NULL AND ";
                } else {
                    if ($schema[$colName]['DATA_TYPE'] === 'DATE') {
                        $data_size = isset($schema[$colName]['DATA_SIZE']) ? empty($schema[$colName]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$colName]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                        $sql_where .= "= TO_DATE(:$colName, '$data_size') AND ";
                    } elseif ($schema[$colName]['DATA_TYPE'] === 'TIMESTAMP WITH TIME ZONE' || $schema[$colName]['DATA_TYPE'] === 'TIMESTAMP WITH LOCAL TIME ZONE') {
                        $data_size = isset($schema[$colName]['DATA_SIZE']) ? empty($schema[$colName]['DATA_SIZE']) ? 'YYYY-MM-DD HH24:MI:SS' : $schema[$colName]['DATA_SIZE'] : 'YYYY-MM-DD HH24:MI:SS';
                        $sql_where .= "= TO_TIMESTAMP(:$colName, '$data_size') AND ";
                    } else {
                        $sql_where .= "= :$colName AND ";
                    }
                    $data_where[$colName] = $value;
                }
            }
        }
        $sql_where = substr(trim($sql_where), 0, -4);

        $sqlCommand = "\nWHERE $sql_where";
        return $sql = ['command' => $sqlCommand, 'data' => $data_where];
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