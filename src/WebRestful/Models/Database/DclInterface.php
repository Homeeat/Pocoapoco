<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database;


interface DclInterface
{

    /**
     * Commit.
     *
     * @param string $serverName
     *
     * @return void
     */
    public static function commit(string $serverName);

    /**
     * Rollback.
     *
     * @param string $serverName
     *
     * @return void
     */
    public static function rollback(string $serverName);

}