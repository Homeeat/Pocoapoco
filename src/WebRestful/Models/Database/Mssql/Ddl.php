<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Mssql;

use Ntch\Pocoapoco\WebRestful\Models\Database\Mssql\Base as MssqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DdlInterface;

class Ddl extends MssqlBase implements DdlInterface
{

    /**
     * @inheritDoc
     */
    public static function createTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $table = self::$databaseObject[$mvc][$modelName]->tableName;
        $schema = self::$databaseObject[$mvc][$modelName]->schema;
        $permission = $schemaName;
        $user = $userName;

        $SQL = "CREATE TABLE \"$permission\".\"$table\" (\n";
        $SQL_KEY = '';
        foreach ($schema as $columnName => $info) {
            $dataType = $info['DATA_TYPE'];
            $dataSize = isset($info['DATA_SIZE']) ? empty($info['DATA_SIZE']) || $info['DATA_TYPE'] === 'datetime' ? null : $info['DATA_SIZE'] : null;
            $nullAble = isset($info['NULLABLE']) ? $info['NULLABLE'] === 'Y' ? ' NULL' : ' NOT NULL' : null;
            $type_size = is_null($dataSize) ? "$dataType" : "$dataType($dataSize)";
            switch ($dataType) {
                case 'char':
                case 'varchar':
                case 'nchar':
                case 'nvarchar':
                    $dataDefault = isset($info['DATA_DEFAULT']) ? empty($info['DATA_DEFAULT']) ? null : ' DEFAULT \'' . $info['DATA_DEFAULT'] . '\'' : null;
                    break;
                case 'tinyint':
                case 'smallint':
                case 'bigint':
                case 'int':
                case 'float':
                case 'decimal':
                case 'datetime':
                case 'date':
                    $dataDefault = isset($info['DATA_DEFAULT']) ? empty($info['DATA_DEFAULT']) ? null : ' DEFAULT ' . $info['DATA_DEFAULT'] : null;
                    break;
                default:
                    die("【ERROR】Not support data Type： $dataType");
            }
            $SQL .= "\t\"$columnName\"\t$type_size$dataDefault$nullAble,\n";

            $keyType = isset($info['KEY_TYPE']) ? $info['KEY_TYPE'] : '';
            if (!empty($keyType)) {
                switch ($keyType) {
                    case 'P':
                        $pk = $table . '_PK';
                        $SQL_KEY .= "\tCONSTRAINT \"$pk\" PRIMARY KEY CLUSTERED(\"$columnName\"),\n";
                        break;
                    default:
                        die("【ERROR】Not support key Type： $keyType");
                }
            }
        }
        $SQL = substr($SQL . $SQL_KEY, 0, -2) . "\n);\n";
        return $SQL;
    }

    /**
     * @inheritDoc
     */
    public static function dropTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {

    }

    /**
     * @inheritDoc
     */
    public static function alterTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {

    }

    /**
     * @inheritDoc
     */
    public static function truncateTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {

    }

    /**
     * @inheritDoc
     */
    public static function commentTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {
        self::tableOnly(self::$databaseObject[$mvc][$modelName]->modelType, __FUNCTION__);

        // config
        $table = self::$databaseObject[$mvc][$modelName]->tableName;
        $schema = self::$databaseObject[$mvc][$modelName]->schema;
        $permission = $schemaName;
        $user = $userName;

        $SQL = '';
        foreach ($schema as $columnName => $info) {
            $comments = isset($info['COMMENT']) ? $info['COMMENT'] : null;
            $SQL .= "execute sp_addextendedproperty 'MS_Description','$comments','SCHEMA','$permission','table','$table','column','$columnName';\n";
        }
        return $SQL;
    }

    /**
     * @inheritDoc
     */
    public static function renameTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName)
    {

    }

}