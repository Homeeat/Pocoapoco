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
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function createTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Drop table.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function dropTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Alter table.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function alterTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Truncate table.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function truncateTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Comment table.
     * For Oracle、Mssql、Postgres
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function commentTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

    /**
     * Rename table.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string|null $schemaName
     * @param string $userName
     * @param string $tableName
     *
     * @return string
     */
    public static function renameTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName);

}