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
use Ntch\Pocoapoco\WebRestful\Models\Database\DclInterface;

class Dcl extends PostgresBase implements DclInterface
{

    /**
     * @inheritDoc
     */
    public static function commit(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @pg_query($serverResult, "COMMIT;");
        }
    }

    /**
     * @inheritDoc
     */
    public static function rollback(string $serverName)
    {
        if (self::$serverObject[$serverName]['status'] === 'success') {
            $serverResult = self::$serverObject[$serverName]['result'];
            @pg_query($serverResult, 'ROLLBACK;');
        }
    }

}