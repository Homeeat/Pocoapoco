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
use Ntch\Pocoapoco\WebRestful\Models\Database\DdlInterface;

class Ddl extends OracleBase implements DdlInterface
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
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList[$mvc]['oracle']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['oracle']['table'][$modelName]['table'];
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->schema;
        }
        $user = self::$databaseList[$mvc]['oracle']['server'][$serverName]['user'];

        $SQL = "CREATE TABLE $user.$table (\n";
        $SQL_KEY = '';
        foreach ($schema as $columnName => $info) {
            $dataType = $info['DATA_TYPE'];
            $dataSize = isset($info['DATA_SIZE']) ? empty($info['DATA_SIZE']) || $info['DATA_TYPE'] === 'DATE' ? null : $info['DATA_SIZE'] : null;
            $nullAble = isset($info['NULLABLE']) ? $info['NULLABLE'] === 'Y' ? null : ' NOT NULL' : null;
            $type_size = is_null($dataSize) ? "$dataType" : "$dataType($dataSize)";
            switch ($dataType) {
                case 'CHAR':
                case 'NCHAR':
                case 'VARCHAR2':
                case 'NVARCHAR2':
                case 'NCLOB':
                    $dataDefault = isset($info['DATA_DEFAULT']) ? empty($info['DATA_DEFAULT']) ? null : ' DEFAULT \'' . $info['DATA_DEFAULT'] . '\'' : null;
                    break;
                case 'FLOAT':
                case 'NUMBER':
                    $dataDefault = isset($info['DATA_DEFAULT']) ? empty($info['DATA_DEFAULT']) ? null : ' DEFAULT ' . $info['DATA_DEFAULT'] : null;
                    break;
                case strpos($dataType, 'TIMESTAMP'):
                case 'DATE':
                case 'TIMESTAMP':
                case 'TIMESTAMP WITH TIME ZONE':
                case 'TIMESTAMP WITH LOCAL TIME ZONE':
                    $dataDefault = isset($info['DATA_DEFAULT']) ? empty($info['DATA_DEFAULT']) ? null : ' DEFAULT SYSDATE' : null;
                    break;
                default:
                    die("【ERROR】Not support data Type： $dataType");
            }
            $SQL .= "\t$columnName $type_size$dataDefault$nullAble,\n";

            $keyType = isset($info['KEY_TYPE']) ? $info['KEY_TYPE'] : '';
            if (!empty($keyType)) {
                switch ($keyType) {
                    case 'P':
                        $pk = $table . '_PK';
                        $SQL_KEY .= "\tCONSTRAINT $pk PRIMARY KEY ($columnName),\n";
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
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->$tableName->schema;
        } else {
            $serverName = self::$databaseList[$mvc]['oracle']['table'][$modelName]['server'];
            $table = self::$databaseList[$mvc]['oracle']['table'][$modelName]['table'];
            $schema = self::$databaseObject[$mvc]['oracle']->$modelType[$modelName]->schema;
        }
        $user = self::$databaseList[$mvc]['oracle']['server'][$serverName]['user'];

        $SQL = '';
        foreach ($schema as $columnName => $info) {
            $comments = isset($info['COMMENT']) ? $info['COMMENT'] : null;
            $SQL .= "COMMENT ON COLUMN $user.$table.$columnName IS '$comments';\n";
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