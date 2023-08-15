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

interface DmlInterface
{

    /**
     * Insert data.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function insert(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Insert values.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     * @param array $data_bind
     *
     * @return string
     */
    public static function values(string $mvc, string $modelName, string $tableName, array $data, array $data_bind);

    /**
     * Delete data.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function delete(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Update data.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function update(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Update set.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     * @param array $data_bind
     *
     * @return array
     */
    public static function set(string $mvc, string $modelName, string $tableName, array $data, array $data_bind);

}