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
use Ntch\Pocoapoco\WebRestful\Models\Database\DclInterface;

class Dcl extends PostgreBase implements DclInterface
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
            $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList['postgre']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList['postgre']['server'][$serverName]['connect']['result'];
            @pg_query($serverResult, "COMMIT;");
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
            $serverName = self::$databaseList['postgre']['table'][$modelName]['server'];
        }

        $serverStatus = self::$databaseList['postgre']['server'][$serverName]['connect']['status'];
        if ($serverStatus === 'success') {
            $serverResult = self::$databaseList['postgre']['server'][$serverName]['connect']['result'];
            @pg_query($serverResult, 'ROLLBACK;');
        }
    }

}