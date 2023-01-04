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

interface DdlInterface
{

    /**
     * Create table.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function createTable(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Drop table.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function dropTable(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Alter table.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function alterTable(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Truncate table.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function truncateTable(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Comment table.
     * For Oracle、Mssql、Postgre
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function commentTable(string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Rename table.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function renameTable(string $modelType, string $modelName, string $tableName, string $mvc);

}