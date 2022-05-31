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
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     *
     * @return string
     */
    public static function insert(string $modelType, string $modelName, string $tableName);

    /**
     * Insert value.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     * @param array $data_bind
     *
     * @return string
     */
    public static function value(string $modelType, string $modelName, string $tableName, array $data, array $data_bind);

    /**
     * Delete data.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     *
     * @return string
     */
    public static function delete(string $modelType, string $modelName, string $tableName);

    /**
     * Update data.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     *
     * @return string
     */
    public static function update(string $modelType, string $modelName, string $tableName);

    /**
     * Update set.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     * @param array $data_bind
     *
     * @return array
     */
    public static function set(string $modelType, string $modelName, string $tableName, array $data, array $data_bind);

}