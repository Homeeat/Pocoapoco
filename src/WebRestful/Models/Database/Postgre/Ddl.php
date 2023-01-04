<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Postgre;

use Ntch\Pocoapoco\WebRestful\Models\Database\Postgre\Base as PostgreBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DdlInterface;

class Ddl extends PostgreBase implements DdlInterface
{

    /**
     * @inheritDoc
     */
    public static function createTable(string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if ($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
            $schema = self::$databaseObject[$mvc]['postgre']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList[$mvc]['postgre']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgre']['table'][$modelName]['table'];
            $schema = self::$databaseObject[$mvc]['postgre']->$modelType[$modelName]->schema;
        }
        $permission = self::$databaseList[$mvc]['postgre']['server'][$serverName]['schema'];
        $user = self::$databaseList[$mvc]['postgre']['server'][$serverName]['user'];

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
                    $dataDefault = isset($info['DATA_DEFAULT']) ? empty($info['DATA_DEFAULT']) ? null : ' DEFAULT \'' . $info['DATA_DEFAULT'] . '\'' : null;
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
                    $dataDefault = isset($info['DATA_DEFAULT']) ? empty($info['DATA_DEFAULT']) ? null : ' DEFAULT ' . $info['DATA_DEFAULT'] : null;
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
    public static function dropTable(string $modelType, string $modelName, string $tableName, string $mvc)
    {

    }

    /**
     * @inheritDoc
     */
    public static function alterTable(string $modelType, string $modelName, string $tableName, string $mvc)
    {

    }

    /**
     * @inheritDoc
     */
    public static function truncateTable(string $modelType, string $modelName, string $tableName, string $mvc)
    {

    }

    /**
     * @inheritDoc
     */
    public static function commentTable(string $modelType, string $modelName, string $tableName, string $mvc)
    {
        // config
        if ($modelType === 'server') {
            $serverName = $modelName;
            $table = $tableName;
            $schema = self::$databaseObject[$mvc]['postgre']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList[$mvc]['postgre']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['postgre']['table'][$modelName]['table'];
            $schema = self::$databaseObject[$mvc]['postgre']->$modelType[$modelName]->schema;
        }
        $permission = self::$databaseList['postgre']['server'][$serverName]['schema'];
        $user = self::$databaseList[$mvc]['postgre']['server'][$serverName]['user'];

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
    public static function renameTable(string $modelType, string $modelName, string $tableName, string $mvc)
    {

    }

}