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
    public static function commit(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @oci_commit($serverResult);
        }
    }

    /**
     * @inheritDoc
     */
    public static function rollback(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @oci_rollback($serverResult);
        }
    }

}