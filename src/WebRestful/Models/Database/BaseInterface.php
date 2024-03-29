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

interface BaseInterface
{

    /**
     * Execute.
     *
     * @param array $serverList
     * @param string $mvc
     *
     * @return void
     */
    public function execute(array $serverList, string $mvc);

    /**
     * Connect driver.
     *
     * @param array $driverConfig
     *
     * @return resource
     */
    public function connect(array $driverConfig);

    /**
     * Check driver config.
     *
     * @param string $serverName
     * @param array $driver
     *
     * @return void
     */
    public function checkDriverConfig(string $serverName, array $driver);

    /**
     * Load model user define in object schema method.
     *
     * @param string $mvc
     *
     * @return void
     */
    public function loadModelUserSchema(string $mvc, string $serverName);

    /**
     * Select all_tab_columns.
     *
     * @param string $serverName
     * @param string $serachName
     *
     * @return array
     */
    public function allTabColumns(string $serverName, string $serachName, string $mvc);

    /**
     * Query.
     *
     * @param string $serverName
     * @param string $mvc
     * @param string $modelName
     * @param string|null $tableName
     * @param string $sqlCommand
     * @param array|null $sqlData
     * @param array $sqlData_bind
     * @param string|null $keyName
     * @param int $offset
     * @param int $limit
     * @param bool $query_pass
     *
     * @return array
     */
    public static function query(string $serverName, string $mvc, string $modelName, ?string $tableName, string $sqlCommand, ?array $sqlData, array $sqlData_bind, ?string $keyName, int $offset, int $limit, bool $query_pass);

    /**
     * System set data from model setting.
     *
     * @param string $action
     * @param array $schema
     * @param array $data
     *
     * @return array
     */
    public static function systemSet(string $action, array $schema, array $data);

    /**
     * Data bind.
     *
     * @param string $mvc
     * @param string $modelName
     * @param string $tableName
     * @param array $sqlBind
     * @param bool $query_pass
     *
     * @return array
     */
    public static function dataBind(string $mvc, string $modelName, string $tableName, array $sqlBind, bool $query_pass);

}