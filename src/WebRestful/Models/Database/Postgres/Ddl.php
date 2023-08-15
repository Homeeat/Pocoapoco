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
use Ntch\Pocoapoco\WebRestful\Models\Database\DdlInterface;

class Ddl extends PostgresBase implements DdlInterface
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

        $SQL = "CREATE TABLE $permission.$table (\n";
        $SQL_KEY = '';
        foreach ($schema as $columnName => $info) {
            $dataType = $info['DATA_TYPE'];
            $dataSizeNullType = ['date', 'timestamp', 'timestamptz'];
            $dataSize = isset($info['DATA_SIZE']) ? empty($info['DATA_SIZE']) || in_array($info['DATA_TYPE'], $dataSizeNullType) ? null : $info['DATA_SIZE'] : null;
            $nullAble = isset($info['NULLABLE']) ? $info['NULLABLE'] === 'Y' ? null : ' NOT NULL' : null;
            $comment = isset($info['COMMENT']) ? empty($info['COMMENT']) ? null : ' COMMENT \'' . $info['COMMENT'] . '\'' : null;
            $type_size = is_null($dataSize) ? "$dataType" : "$dataType($dataSize)";
            $dataCheck = isset($info['DATA_CHECK']) ? empty($info['DATA_CHECK']) ? null : ' CHECK (' . $info['DATA_CHECK'] . ')' : null;
            switch ($dataType) {
                case 'char':
                case 'varchar':
                    $dataDefault = isset($info['DATA_DEFAULT']) ? ' DEFAULT \'' . $info['DATA_DEFAULT'] . '\'' : null;
                    break;
                case 'uuid':
                case 'bool':
                case 'date':
                case 'inet':
                case 'json':
                case 'float':
                case 'decimal':
                case 'integer':
                case 'bigint':
                case 'smallint':
                case 'timestamp':
                case 'timestamptz':
                case 'xml':
                    $dataDefault = isset($info['DATA_DEFAULT']) ? ' DEFAULT ' . $info['DATA_DEFAULT'] : null;
                    break;
                default:
                    die("【ERROR】Not support data Type： $dataType");
            }
            $SQL .= "\t$columnName $type_size$dataDefault$nullAble$dataCheck,\n";

            $keyType = isset($info['KEY_TYPE']) ? $info['KEY_TYPE'] : '';
            if (!empty($keyType)) {
                switch ($keyType) {
                    case 'P':
                        $pk = $table . '_PK';
                        $SQL_KEY .= "\tPRIMARY KEY ($columnName),\n";
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

        $SQL = '';
        foreach ($schema as $columnName => $info) {
            $comments = isset($info['COMMENT']) ? $info['COMMENT'] : null;
            $SQL .= "COMMENT ON COLUMN $permission.$table.$columnName IS '$comments';\n";
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