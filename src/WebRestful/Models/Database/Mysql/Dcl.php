<?php


namespace Ntch\Pocoapoco\WebRestful\Models\Database\Mysql;

use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Base as MysqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DclInterface;

class Dcl extends MysqlBase implements DclInterface
{

    /**
     * @inheritDoc
     */
    public static function commit(string $modelType, string $modelName)
    {
        // config
        if ($modelType === 'server') {
            $serverName = $modelName;
        } else {
            $serverName = self::$databaseList['mysql']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList['mysql']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList['mysql']['server'][$serverName]['connect']['result'];
            @mysqli_commit($serverResult);
        }
    }

    /**
     * @inheritDoc
     */
    public static function rollback(string $modelType, string $modelName)
    {
        // config
        if ($modelType === 'server') {
            $serverName = $modelName;
        } else {
            $serverName = self::$databaseList['mysql']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList['mysql']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList['mysql']['server'][$serverName]['connect']['result'];
            @mysqli_rollback($serverResult);
        }
    }

}