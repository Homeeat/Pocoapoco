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
use Ntch\Pocoapoco\WebRestful\Models\Database\DclInterface;

class Dcl extends MssqlBase implements DclInterface
{

    /**
     * @inheritDoc
     */
    public static function commit(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @sqlsrv_commit($serverResult);
        }
    }

    /**
     * @inheritDoc
     */
    public static function rollback(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @sqlsrv_rollback($serverResult);
        }
    }

}