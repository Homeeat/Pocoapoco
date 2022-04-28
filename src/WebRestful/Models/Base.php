<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
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
     *
     * @return void
     */
    public function modelBase(string $driver, array $models)
    {
        $this->settingBase = new SettingBase();
        $this->setDriverList($driver, $models);
        $this->checkModelConfig($driver, $models);
        $this->createModelObject($driver, $models);

        switch ($driver) {
            case 'oracle':
                $oracle = new OracleBase();
                $oracle->execute();
                break;
            case 'mysql':
                $mysql = new MysqlBase();
                $mysql->execute();
                break;
            case 'mssql':
                $mssql = new MssqlBase();
                $mssql->execute();
                break;
            case 'postgre':
                $postgre = new PostgreBase();
                $postgre->execute();
                break;
        }

        $this->loadWebRestful($driver, $models);
        $this->loadModelTableSchema($driver);

    }

    /**
     * Set models driver list.
     *
     * @param string $driver
     * @param array $models
     *
     * @return void
     */
    private function setDriverList(string $driver, array $models)
    {
        $settingList = $this->settingBase->getSettingData($driver);
        $serverNameList = [];

        foreach ($models as $key) {
            // if - table , else - server
            if (isset($settingList[$key]['server']) && isset($settingList[$key]['type']) && $settingList[$key]['type'] == 'table') {
                $serverName = $settingList[$key]['server'];

                if (!in_array($serverName, $serverNameList)) {
                    if (isset($settingList[$serverName]['type']) && $settingList[$serverName]['type'] == 'server') {
                        self::$databaseList[$driver]['server'][$serverName] = $settingList[$serverName];
                        array_push($serverNameList, $serverName);
                    } else {
                        die("【ERROR】Models $driver.ini server \"$serverName\" is not exist.");
                    }
                }

                self::$databaseList[$driver]['table'][$key] = $settingList[$key];
            } else {
                if (!in_array($key, $serverNameList)) {
                    if (isset($settingList[$key]['type']) && $settingList[$key]['type'] == 'server') {
                        self::$databaseList[$driver]['server'][$key] = $settingList[$key];
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
     *
     * @return void
     */
    private function checkModelConfig(string $driver, array $models)
    {
        $modelConfigList = ['path', 'class'];

        foreach ($models as $key) {
            foreach ($modelConfigList as $key2) {
                (isset(self::$databaseList[$driver]['server'][$key][$key2]) || isset(self::$databaseList[$driver]['table'][$key][$key2])) ? null : die("【ERROR】Model $key tag \"$key2\" is not exist.");
            }
        }
    }

    /**
     * Create model and driver object.
     *
     * @param string $driver
     * @param array $models
     *
     * @return void
     */
    private function createModelObject(string $driver, array $models)
    {
        self::$databaseObject[$driver] = new \stdClass();

        // server
        foreach (self::$databaseList[$driver]['server'] as $serverName => $serverInfo) {
            foreach ($models as $modelName) {
                if ($serverName === $modelName) {
                    self::$databaseObject[$driver]->server[$serverName] = new \stdClass();
                }
            }
        }

        // table
        if (isset(self::$databaseList[$driver]['table'])) {
            foreach (self::$databaseList[$driver]['table'] as $tableName => $tableInfo) {
                self::$databaseObject[$driver]->table[$tableName] = new \stdClass();
            }
        }
    }

    /**
     * Load webRestful.
     *
     * @param string $driver
     * @param array $models
     *
     * @return void
     */
    private function loadWebRestful(string $driver, array $models)
    {
        // server
        foreach (self::$databaseList[$driver]['server'] as $serverName => $serverInfo) {
            foreach ($models as $modelName) {
                if ($serverName === $modelName) {
                    $this->webRestfulCheckList('model', null, $serverInfo['path'], $serverInfo['class'], null);

                    $tableNames = array_keys((array)self::$databaseObject[$driver]->server[$serverName]);
                    foreach ($tableNames as $tableName) {
                        $schema = self::$databaseObject[$driver]->server[$serverName]->$tableName->schema;
                        self::$databaseObject[$driver]->server[$serverName]->$tableName = new $serverInfo['class']();
                        self::$databaseObject[$driver]->server[$serverName]->$tableName->tableName = $tableName;
                        self::$databaseObject[$driver]->server[$serverName]->$tableName->schema = $schema;
                    }
                }
            }
        }

        // table
        if (isset(self::$databaseList[$driver]['table'])) {
            foreach (self::$databaseList[$driver]['table'] as $tableName => $tableInfo) {
                $this->webRestfulCheckList('model', null, $tableInfo['path'], $tableInfo['class'], 'schema');

                $modelObjet = new $tableInfo['class']();
                self::$databaseObject[$driver]->table[$tableName] = $modelObjet;
                self::$databaseObject[$driver]->table[$tableName]->tableName = self::$databaseList[$driver]['table'][$tableName]['table'];
            }
        }
    }

    /**
     * Load model table define in object schema method.
     *
     * @param string $driver
     *
     * @return void
     */
    private function loadModelTableSchema(string $driver)
    {
        if (isset(self::$databaseObject[$driver]->table)) {
            foreach (self::$databaseObject[$driver]->table as $tableName => $tableInfo) {
                $modelObject = self::$databaseObject[$driver]->table[$tableName];
                self::$databaseObject[$driver]->table[$tableName]->schema = $modelObject->schema();
            }
        }
    }

    /**
     * Get databaseList.
     * Password masking.
     *
     * @return array
     */
    public function getDatabaseList(): array
    {
        $driveList = ['oracle', 'mysql', 'mssql', 'postgre'];
        $showData = self::$databaseList;

        foreach ($driveList as $dbName => $type) {
            if (isset($showData[$dbName])) {
                foreach ($showData[$dbName]['server'] as $serverName => $serverTag) {
                    $showData[$dbName]['server'][$serverName]['password'] = '***************';
                }
            }
        }
        return $showData;
    }

    /**
     * Get database object.
     *
     * @return array
     */
    public function getDatabaseObject(): array
    {
        return self::$databaseObject;
    }

}