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
     * @param string $mvc
     *
     * @return string
     */
    public static function insert(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Insert value.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     * @param array $data_bind
     * @param string $mvc
     *
     * @return string
     */
    public static function value(string $modelType, string $modelName, string $tableName, array $data, array $data_bind, string $mvc);

    /**
     * Delete data.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function delete(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Update data.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function update(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Update set.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     * @param array $data_bind
     * @param string $mvc
     *
     * @return array
     */
    public static function set(string $modelType, string $modelName, string $tableName, array $data, array $data_bind, string $mvc);

}