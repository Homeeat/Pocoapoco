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

use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Base as OracleBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Ddl;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Dml;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Dql;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Dcl;

class OracleModel
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
                        $result = OracleBase::query($this->modelType, $this->modelName, $this->tableName, $this->sql, $this->data, $this->keyName, $this->offset, $this->limit);
                        break;
                    case 1:
                        $result = OracleBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $sqlBind = null, $this->keyName, $this->offset, $limit = -1);
                        break;
                    case 2:
                        $result = OracleBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $this->keyName, $this->offset, $limit = -1);
                        break;
                    case 3:
                        $result = OracleBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $args[2], $this->offset, $limit = -1);
                        break;
                    case 4:
                        $result = OracleBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $args[2], $args[3], $limit = -1);
                        break;
                    case 5:
                        $result = OracleBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $args[2], $args[3], $args[4]);
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
        $this->offset = $offset;
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
        $this->limit = $limit;
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

    /**
     * Comment table.
     *
     * @return string
     */
    public function commentTable()
    {
        return Ddl::commentTable($this->modelType, $this->modelName, $this->tableName);
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
        if ($this->action === 'INSERT') {
            $this->sql = Dml::insert($this->modelType, $this->modelName, $this->tableName);
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeInsert();
        }

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
        if ($this->action === 'INSERT') {
            $res = Dml::value($this->modelType, $this->modelName, $this->tableName, $data);
            $this->sql .= $res['command'];
            $this->data = array_merge($this->data, $res['data']);
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeValue($this->modelType, $this->modelName, $this->tableName, $data);
        }

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
        if ($this->action === 'DELETE') {
            $this->sql = Dml::delete($this->modelType, $this->modelName, $this->tableName);
        } elseif ($this->action === 'MERGE') {

        }

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
        if ($this->action === 'UPDATE') {
            $this->sql = Dml::update($this->modelType, $this->modelName, $this->tableName);
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeUpdate();
        }

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
        if ($this->action === 'UPDATE') {
            $res = Dml::set($this->modelType, $this->modelName, $this->tableName, $data);
            $this->sql .= $res['command'];
            $this->data = array_merge($this->data, $res['data']);
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeSet($this->modelName, $data);
        }

        return $this;
    }

    /**
     * Merge table.
     *
     * @return object
     */
    public function merge()
    {
        empty($this->action) ? $this->action = 'MERGE' : null;
        $this->sql = Dml::merge($this->modelType, $this->modelName, $this->tableName);

        return $this;
    }

    /**
     * Merge using.
     *
     * @param string $user
     * @param string $tableName
     *
     * @return object
     */
    public function using(string $user, string $tableName)
    {
        $this->sql .= Dml::using($user, $tableName);

        return $this;
    }

    /**
     * Merge on.
     *
     * @param string $target
     * @param string $source
     *
     * @return object
     */
    public function on(string $target, string $source)
    {
        $this->sql .= Dml::on($target, $source);

        return $this;
    }

    /**
     * Merge matched.
     *
     * @return object
     */
    public function matched()
    {
        $this->sql .= Dml::matched();

        return $this;
    }

    /**
     * Merge not matched.
     *
     * @return object
     */
    public function not()
    {
        $this->sql .= Dml::not();

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