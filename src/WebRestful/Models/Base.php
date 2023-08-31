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
use Ntch\Pocoapoco\WebRestful\Models\Database\Postgres\Base as PostgresBase;

class Base extends WebRestful
{

    /**
     * @var SettingBase
     */
    protected $settingBase;

    /**
     * @var array
     */
    protected static array $serverList = [];

    /**
     * @var array
     */
    protected static array $serverObject = [];

    /**
     * @var array
     */
    protected static array $databaseList = [];

    /**
     * @var array
     */
    protected static array $databaseObject = [];

    /**
     * @var array
     */
    private static array $driverList = ['oracle', 'mysql', 'mssql', 'postgres'];

    /**
     * Model entry point.
     *
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    public function modelBase(array $models, string $mvc)
    {
        $this->settingBase = new SettingBase();
        self::$serverList[$mvc] = array();
        $this->setList($models, $mvc);
        $this->createModelObject($mvc);

        $serverList = array();
        foreach (self::$serverList[$mvc] as $serverName => $serverConfig) {
            $serverList[$serverConfig['driver']][$serverName] = $serverConfig;
        }

        if (!empty($serverList['oracle'])) {
            $oracle = new OracleBase();
            $oracle->execute($serverList['oracle'], $mvc);
        }
        if (!empty($serverList['mysql'])) {
            $mysql = new MysqlBase();
            $mysql->execute($serverList['mysql'], $mvc);
        }
        if (!empty($serverList['mssql'])) {
            $mssql = new MssqlBase();
            $mssql->execute($serverList['mssql'], $mvc);
        }
        if (!empty($serverList['postgres'])) {
            $postgres = new PostgresBase();
            $postgres->execute($serverList['postgres'], $mvc);
        }

        $this->loadWebRestful($models, $mvc);
    }

    /**
     * Set models driver list.
     *
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    private function setList(array $models, string $mvc)
    {
        $settingList = $this->settingBase->getSettingData('models');
        foreach ($models as $key) {

            // check config
            isset($settingList[$key]['type']) ? null : die("【ERROR】Model $key tag is not exist.");
            // check config type
            if ($settingList[$key]['type'] == 'server') {
                // check config driver
                isset($settingList[$key]['driver']) ? null : die("【ERROR】Model $key tag driver is not exist.");
                in_array($settingList[$key]['driver'], self::$driverList) ? null : die("【ERROR】Model $key driver is not support.");
                if (!in_array($key, self::$serverList[$mvc])) {
                    self::$serverList[$mvc][$key] = $settingList[$key];
                }
            } elseif ($settingList[$key]['type'] == 'table') {
                // check config server
                isset($settingList[$key]['server']) ? null : die("【ERROR】Model $key tag server is not exist.");
                $serverName = $settingList[$key]['server'];
                if (!in_array($serverName, self::$serverList[$mvc])) {
                    if (isset($settingList[$serverName]['type']) && $settingList[$serverName]['type'] == 'server') {
                        isset($settingList[$serverName]['driver']) ? null : die("【ERROR】Model $serverName tag driver is not exist.");
                        self::$serverList[$mvc][$serverName] = $settingList[$serverName];
                    } else {
                        die("【ERROR】Models model.ini server \"$serverName\" is not exist.");
                    }
                }
            } else {
                die("【ERROR】Model $key tag type is not support.");
            }

            foreach (['path', 'class'] as $key2) {
                isset($settingList[$key][$key2]) ? null : die("【ERROR】Model $key tag \"$key2\" is not exist.");
            }
            // set model
            self::$databaseList[$mvc][$key] = $settingList[$key];
        }
    }

    /**
     * Create model and driver object.
     *
     * @param string $mvc
     *
     * @return void
     */
    private function createModelObject(string $mvc)
    {
        foreach (self::$databaseList[$mvc] as $key => $Info) {
            if (empty(self::$databaseObject[$mvc][$key])) {
                self::$databaseObject[$mvc][$key] = new \stdClass();
            }
        }
    }

    /**
     * Load webRestful.
     *
     * @param array $models
     * @param string $mvc
     *
     * @return void
     */
    private function loadWebRestful(array $models, string $mvc)
    {
        foreach ($models as $modelName) {
            $modelInfo = self::$databaseList[$mvc][$modelName];
            if ($modelInfo['type'] == 'server') {
                $serverName = $modelName;
                self::$databaseObject[$mvc][$modelName] = $this->webRestfulCheckList('model', null, $modelInfo['path'], $modelInfo['class'], null);
            } else {
                $serverName = self::$databaseList[$mvc][$modelName]['server'];
                self::$databaseObject[$mvc][$modelName] = $this->webRestfulCheckList('model', null, $modelInfo['path'], $modelInfo['class'], 'schema');
                self::$databaseObject[$mvc][$modelName]->schema = self::$databaseObject[$mvc][$modelName]->schema();
                self::$databaseObject[$mvc][$modelName]->tableName = self::$databaseList[$mvc][$modelName]['table'];
            }
            self::$databaseObject[$mvc][$modelName]->mvc = $mvc;
            self::$databaseObject[$mvc][$modelName]->modelType = self::$databaseList[$mvc][$modelName]['type'];
            self::$databaseObject[$mvc][$modelName]->modelName = $modelName;
            switch (self::$serverList[$mvc][$serverName]['driver']) {
                case 'mssql':
                    if (self::$databaseList[$mvc][$modelName]['type'] === 'table') {
                        self::$databaseObject[$mvc][$modelName]->schemaName = empty(self::$databaseList[$mvc][$modelName]['schema']) ? 'dbo' : self::$databaseList[$mvc][$modelName]['schema'];
                    }
                    break;
                case 'postgres':
                    if (self::$databaseList[$mvc][$modelName]['type'] === 'table') {
                        self::$databaseObject[$mvc][$modelName]->schemaName = self::$databaseList[$mvc][$modelName]['schema'];
                    }
                    break;
            }
            self::$databaseObject[$mvc][$modelName]->userName = self::$serverList[$mvc][$serverName]['user'];
            self::$databaseObject[$mvc][$modelName]->server = clone self::$serverObject[$serverName]['server'];
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
        $showData = isset(self::$databaseList[$mvc]) ? self::$databaseList[$mvc] : [];

        if (!empty($showData)) {
            foreach (self::$driverList as $dbName => $type) {
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
    public function getDatabaseObject(string $mvc)
    {
        self::$databaseObject[$mvc] = isset(self::$databaseObject[$mvc]) ? self::$databaseObject[$mvc] : [];
        return self::$databaseObject[$mvc];
    }

    protected static function tableOnly(string $type, string $fun)
    {
        if ($type !== 'table') {
            die("【ERROR】Not support $fun");
        }
    }

}