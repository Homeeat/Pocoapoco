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
     * @param string|null $schemaName
     * @param string $userName
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function createTable(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Drop table.
     *
     * @param string|null $schemaName
     * @param string $userName
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function dropTable(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Alter table.
     *
     * @param string|null $schemaName
     * @param string $userName
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function alterTable(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Truncate table.
     *
     * @param string|null $schemaName
     * @param string $userName
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function truncateTable(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Comment table.
     * For Oracle、Mssql、Postgres
     *
     * @param string|null $schemaName
     * @param string $userName
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function commentTable(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc);

    /**
     * Rename table.
     *
     * @param string|null $schemaName
     * @param string $userName
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param string $mvc
     *
     * @return string
     */
    public static function renameTable(?string $schemaName, string $userName, string $modelType, string $modelName, string $tableName, string $mvc);

}