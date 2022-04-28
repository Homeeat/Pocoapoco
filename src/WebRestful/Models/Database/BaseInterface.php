<?php

namespace Ntch\Pocoapoco\WebRestful\Models\Database;

interface BaseInterface
{

    /**
     * Execute.
     *
     * @return void
     */
    public function execute();

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
     * @return void
     */
    public function loadModelUserSchema();

    /**
     * Select all_tab_columns.
     *
     * @param string $serverName
     * @param string $serachName
     *
     * @return array
     */
    public function allTabColumns(string $serverName, string $serachName);

    /**
     * Query.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string|null $tableName
     * @param string $sqlCommand
     * @param array|null $sqlData
     * @param string|null $keyName
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public static function query(string $modelType, string $modelName, ?string $tableName, string $sqlCommand, ?array $sqlData, ?string $keyName, int $offset, int $limit);

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
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param array $sqlBind
     *
     * @return array
     */
    public static function dataBind(string $modelType, string $modelName, string $tableName, array $sqlBind);

}