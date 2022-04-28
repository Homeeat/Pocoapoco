<?php


namespace Ntch\Pocoapoco\WebRestful\Models\Database\Oracle;

use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Base as OracleBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DclInterface;

class Dcl extends OracleBase implements DclInterface
{

    /**
     * @inheritDoc
     */
    public static function commit(string $modelType, string $modelName)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
        } else {
            $serverName = self::$databaseList['oracle']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList['oracle']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList['oracle']['server'][$serverName]['connect']['result'];
            @oci_commit($serverResult);
        }
    }

    /**
     * @inheritDoc
     */
    public static function rollback(string $modelType, string $modelName)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
        } else {
            $serverName = self::$databaseList['oracle']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList['oracle']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList['oracle']['server'][$serverName]['connect']['result'];
            @oci_rollback($serverResult);
        }
    }

}