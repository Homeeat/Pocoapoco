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
use Ntch\Pocoapoco\WebRestful\Models\Database\DclInterface;

class Dcl extends OracleBase implements DclInterface
{

    /**
     * @inheritDoc
     */
    public static function commit(string $modelType, string $modelName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
        } else {
            $serverName = self::$databaseList[$mvc]['oracle']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['result'];
            @oci_commit($serverResult);
        }
    }

    /**
     * @inheritDoc
     */
    public static function rollback(string $modelType, string $modelName, string $mvc)
    {
        // config
        if($modelType === 'server') {
            $serverName = $modelName;
        } else {
            $serverName = self::$databaseList[$mvc]['oracle']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList[$mvc]['oracle']['server'][$serverName]['connect']['result'];
            @oci_rollback($serverResult);
        }
    }

}