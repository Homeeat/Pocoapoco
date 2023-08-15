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

interface DqlInterface
{

    /**
     * Select data.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     * @param array $data
     * @param boolean $distinct
     *
     * @return array
     */
    public static function select(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName, array $data, bool $distinct);

    /**
     * Where.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     * @param array $data_bind
     *
     * @return string
     */
    public static function where(string $mvc, string $modelName, string $tableName, array $data, array $data_bind);

    /**
     * Order by.
     *
     * @param array $data
     *
     * @return string
     */
    public static function orderby(array $data);

    /**
     * Group by.
     *
     * @param array $data
     *
     * @return string
     */
    public static function groupby(array $data);

}