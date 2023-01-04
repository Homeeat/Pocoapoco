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
     * @param string $modelType
     * @param string $modelName
     * @param string $mvc
     *
     * @return void
     */
    public static function commit(string $modelType, string $modelName, string $mvc);

    /**
     * Rollback.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $mvc
     *
     * @return void
     */
    public static function rollback(string $modelType, string $modelName, string $mvc);

}