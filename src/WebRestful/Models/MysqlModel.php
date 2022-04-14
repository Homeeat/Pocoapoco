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

use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Base as MysqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Ddl;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Dml;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Dql;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Dcl;

class MysqlModel
{

    /**
     * @var string
     */
    public string $modelType = '';

    /**
     * @var string
     */
    public string $modelName = '';

    /**
     * @var string
     */
    public string $tableName = '';

    /**
     * @var string
     */
    public string $action = '';

    /**
     * @var string
     */
    public string $sql = '';

    /**
     * @var array
     */
    public array $data = [];

    /**
     * @var string|null
     */
    public $keyName = null;

    /**
     * @var int
     */
    public int $offset = 0;

    /**
     * @var int
     */
    public int $limit = -1;

    // Base for query
    function __call(string $fun, array $args)
    {
        $count = count($args);
        switch ($fun) {
            case 'query':
                switch ($count) {
                    case 0:
                        $result = MysqlBase::query($this->modelType, $this->modelName, $this->tableName, $this->sql, $this->data, $this->keyName, $this->offset, $this->limit);
                        break;
                    case 1:
                        $result = MysqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $sqlBind = null, $this->keyName, $this->offset, $limit = -1);
                        break;
                    case 2:
                        $result = MysqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $this->keyName, $this->offset, $limit = -1);
                        break;
                    case 3:
                        $result = MysqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $args[2], $this->offset, $limit = -1);
                        break;
                    case 4:
                        $result = MysqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $args[2], $args[3], $limit = -1);
                        break;
                    case 5:
                        $result = MysqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $args[2], $args[3], $args[4]);
                        break;
                    default:
                        die("【ERROR】Wrong parameters for \"$fun\".");
                }
                $this->clean();
                return $result;
            default:
                die("【ERROR】Not support \"$fun\"");
        }
    }

    /**
     * Clear all set variables.
     *
     * @return void
     */
    public function clean()
    {
        $this->action = '';
        $this->sql = '';
        $this->data = [];
        $this->keyName = null;
        $this->offset = 0;
        $this->limit = -1;
    }

    /**
     * According to the given name as the return key.
     *
     * @param string $keyName
     *
     * @return object
     */
    public function keyName(?string $keyName)
    {
        $this->keyName = $keyName;
        return $this;
    }

    /**
     * Search from the number of the data.
     *
     * @param int $offset
     *
     * @return object
     */
    public function offset(int $offset)
    {
        $this->sql .= "\nOFFSET $offset";
        return $this;
    }

    /**
     * Only search for a specific number.
     *
     * @param int $limit
     *
     * @return object
     */
    public function limit(int $limit)
    {
        $this->sql .= "\nLIMIT $limit";
        return $this;
    }

    // DDL
    /**
     * Create Table.
     *
     * @return string
     */
    public function createTable()
    {
        return Ddl::createTable($this->modelType, $this->modelName, $this->tableName);
    }

    // Dml
    /**
     * Insert data or Merge insert.
     *
     * @return object
     */
    public function insert()
    {
        empty($this->action) ? $this->action = 'INSERT' : null;
        $this->sql = Dml::insert($this->modelType, $this->modelName, $this->tableName);

        return $this;
    }

    /**
     * Insert value or Merge insert value.
     *
     * @param array $data
     *
     * @return object
     */
    public function value(array $data = [])
    {
        $res = Dml::value($this->modelType, $this->modelName, $this->tableName, $data);
        $this->sql .= $res['command'];
        $this->data = array_merge($this->data, $res['data']);

        return $this;
    }

    /**
     * Delete data.
     *
     * @return object
     */
    public function delete()
    {
        empty($this->action) ? $this->action = 'DELETE' : null;
        $this->sql = Dml::delete($this->modelType, $this->modelName, $this->tableName);

        return $this;
    }

    /**
     * Update data or Merge update.
     *
     * @return object
     */
    public function update()
    {
        empty($this->action) ? $this->action = 'UPDATE' : null;
        $this->sql = Dml::update($this->modelType, $this->modelName, $this->tableName);

        return $this;
    }

    /**
     * Update set or Merge update set key.
     *
     * @param array $data
     *
     * @return object
     */
    public function set(array $data = [])
    {
        $res = Dml::set($this->modelType, $this->modelName, $this->tableName, $data);
        $this->sql .= $res['command'];
        $this->data = array_merge($this->data, $res['data']);

        return $this;
    }

    // Dql
    /**
     * Select data.
     *
     * @param array $data
     *
     * @return object
     */
    public function select(array $data = [])
    {
        empty($this->action) ? $this->action = 'SELECT' : null;
        $this->sql = Dql::select($this->modelType, $this->modelName, $this->tableName, $data);

        return $this;
    }

    /**
     * Where.
     *
     * @param array $data
     *
     * @return object
     */
    public function where(array $data)
    {
        $res = Dql::where($this->modelType, $this->modelName, $this->tableName, $data);
        $this->sql .= $res['command'];
        $this->data = array_merge($this->data, $res['data']);
        return $this;
    }

    /**
     * Order by.
     *
     * @param array $data
     *
     * @return object
     */
    public function orderby(array $data)
    {
        $this->sql .= Dql::orderby($data);
        return $this;
    }

    /**
     * Group by.
     *
     * @param array $data
     *
     * @return object
     */
    public function groupby(array $data)
    {
        $this->sql .= Dql::groupby($data);
        return $this;
    }

    // Dcl
    /**
     * Commit.
     *
     * @return void
     */
    public function commit()
    {
        return Dcl::commit($this->modelType, $this->modelName);
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        return Dcl::rollback($this->modelType, $this->modelName);
    }

}