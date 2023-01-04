<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models;

use Ntch\Pocoapoco\WebRestful\WebRestful;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Base as OracleBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Base as MysqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mssql\Base as MssqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Postgre\Base as PostgreBase;

class Base extends WebRestful
{

    /**
     * @var SettingBase
     */
    protected $settingBase;

    /**
     * @var array
     */
    protected static array $databaseList = [];

    /**
     * @var array
     */
    protected static array $databaseObject = [];

    /**
     * Model entry point.
     *
     * @param string $driver
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    public function modelBase(string $driver, array $models, string $mvc)
    {
        $this->settingBase = new SettingBase();
        $this->setDriverList($driver, $models, $mvc);
        $this->checkModelConfig($driver, $models, $mvc);
        $this->createModelObject($driver, $models, $mvc);

        switch ($driver) {
            case 'oracle':
                $oracle = new OracleBase();
                $oracle->execute($mvc);
                break;
            case 'mysql':
                $mysql = new MysqlBase();
                $mysql->execute($mvc);
                break;
            case 'mssql':
                $mssql = new MssqlBase();
                $mssql->execute($mvc);
                break;
            case 'postgre':
                $postgre = new PostgreBase();
                $postgre->execute($mvc);
                break;
        }

        $this->loadWebRestful($driver, $models, $mvc);
        $this->loadModelTableSchema($driver, $mvc);

    }

    /**
     * Set models driver list.
     *
     * @param string $driver
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    private function setDriverList(string $driver, array $models, string $mvc)
    {
        $settingList = $this->settingBase->getSettingData($driver);
        $serverNameList = [];

        foreach ($models as $key) {
            // if - table , else - server
            if (isset($settingList[$key]['server']) && isset($settingList[$key]['type']) && $settingList[$key]['type'] == 'table') {
                $serverName = $settingList[$key]['server'];

                if (!in_array($serverName, $serverNameList)) {
                    if (isset($settingList[$serverName]['type']) && $settingList[$serverName]['type'] == 'server') {
                        self::$databaseList[$mvc][$driver]['server'][$serverName] = $settingList[$serverName];
                        array_push($serverNameList, $serverName);
                    } else {
                        die("【ERROR】Models $driver.ini server \"$serverName\" is not exist.");
                    }
                }

                self::$databaseList[$mvc][$driver]['table'][$key] = $settingList[$key];
            } else {
                if (!in_array($key, $serverNameList)) {
                    if (isset($settingList[$key]['type']) && $settingList[$key]['type'] == 'server') {
                        self::$databaseList[$mvc][$driver]['server'][$key] = $settingList[$key];
                        array_push($serverNameList, $key);
                    } else {
                        die("【ERROR】Models $driver.ini server \"$key\" is not exist.");
                    }
                }
            }
        }
    }

    /**
     * Check model config.
     *
     * @param string $driver
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    private function checkModelConfig(string $driver, array $models, string $mvc)
    {
        $modelConfigList = ['path', 'class'];

        foreach ($models as $key) {
            foreach ($modelConfigList as $key2) {
                (isset(self::$databaseList[$mvc][$driver]['server'][$key][$key2]) || isset(self::$databaseList[$mvc][$driver]['table'][$key][$key2])) ? null : die("【ERROR】Model $key tag \"$key2\" is not exist.");
            }
        }
    }

    /**
     * Create model and driver object.
     *
     * @param string $driver
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    private function createModelObject(string $driver, array $models, string $mvc)
    {
        self::$databaseObject[$mvc][$driver] = new \stdClass();

        // server
        foreach (self::$databaseList[$mvc][$driver]['server'] as $serverName => $serverInfo) {
            foreach ($models as $modelName) {
                if ($serverName === $modelName) {
                    self::$databaseObject[$mvc][$driver]->server[$serverName] = new \stdClass();
                }
            }
        }

        // table
        if (isset(self::$databaseList[$mvc][$driver]['table'])) {
            foreach (self::$databaseList[$mvc][$driver]['table'] as $tableName => $tableInfo) {
                self::$databaseObject[$mvc][$driver]->table[$tableName] = new \stdClass();
            }
        }
    }

    /**
     * Load webRestful.
     *
     * @param string $driver
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    private function loadWebRestful(string $driver, array $models, string $mvc)
    {
        // server
        foreach (self::$databaseList[$mvc][$driver]['server'] as $serverName => $serverInfo) {
            foreach ($models as $modelName) {
                if ($serverName === $modelName) {
                    $classCreate = $this->webRestfulCheckList('model', null, $serverInfo['path'], $serverInfo['class'], null);

                    $tableNames = array_keys((array)self::$databaseObject[$mvc][$driver]->server[$serverName]);
                    foreach ($tableNames as $tableName) {
                        $schema = self::$databaseObject[$mvc][$driver]->server[$serverName]->$tableName->schema;

                        $$tableName = clone $classCreate;
                        self::$databaseObject[$mvc][$driver]->server[$serverName]->$tableName = $$tableName;
                        self::$databaseObject[$mvc][$driver]->server[$serverName]->$tableName->mvc = $mvc;
                        self::$databaseObject[$mvc][$driver]->server[$serverName]->$tableName->tableName = $tableName;
                        self::$databaseObject[$mvc][$driver]->server[$serverName]->$tableName->schema = $schema;
                    }
                }
            }
        }

        // table
        if (isset(self::$databaseList[$mvc][$driver]['table'])) {
            foreach (self::$databaseList[$mvc][$driver]['table'] as $tableName => $tableInfo) {
                $classCreate = $this->webRestfulCheckList('model', null, $tableInfo['path'], $tableInfo['class'], 'schema');
                $classCreate->mvc = $mvc;

                self::$databaseObject[$mvc][$driver]->table[$tableName] = $classCreate;
                self::$databaseObject[$mvc][$driver]->table[$tableName]->tableName = self::$databaseList[$mvc][$driver]['table'][$tableName]['table'];
            }
        }
    }

    /**
     * Load model table define in object schema method.
     *
     * @param string $driver
     * @param string $mvc
     *
     * @return void
     */
    private function loadModelTableSchema(string $driver, string $mvc)
    {
        if (isset(self::$databaseObject[$mvc][$driver]->table)) {
            foreach (self::$databaseObject[$mvc][$driver]->table as $tableName => $tableInfo) {
                $modelObject = self::$databaseObject[$mvc][$driver]->table[$tableName];
                self::$databaseObject[$mvc][$driver]->table[$tableName]->schema = $modelObject->schema();
            }
        }
    }

    /**
     * Get databaseList.
     * Password masking.
     *
     * @param string $mvc
     *
     * @return array
     */
    public function getDatabaseList(string $mvc): array
    {
        $driveList = ['oracle', 'mysql', 'mssql', 'postgre'];
        $showData = isset(self::$databaseList[$mvc]) ? self::$databaseList[$mvc] : [];

        if (!empty($showData)) {
            foreach ($driveList as $dbName => $type) {
                if (isset($showData[$dbName])) {
                    foreach ($showData[$dbName]['server'] as $serverName => $serverTag) {
                        $showData[$dbName]['server'][$serverName]['password'] = '***************';
                    }
                }
            }
        }
        return $showData;
    }

    /**
     * Get database object.
     *
     * @param string $mvc
     *
     * @return array
     */
    public function getDatabaseObject(string $mvc): array
    {
        self::$databaseObject[$mvc] = isset(self::$databaseObject[$mvc]) ? self::$databaseObject[$mvc] : [];
        return self::$databaseObject[$mvc];
    }

}