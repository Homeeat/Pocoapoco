<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Mysql;

use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Base as MysqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\DclInterface;

class Dcl extends MysqlBase implements DclInterface
{

    /**
     * @inheritDoc
     */
    public static function commit(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @mysqli_commit($serverResult);
        }
    }

    /**
     * @inheritDoc
     */
    public static function rollback(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @mysqli_rollback($serverResult);
        }
    }

}