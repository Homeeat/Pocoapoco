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
     * @var array
     */
    public array $data_bind = [];

    /**
     * @var string|null
     */
    public ?string $keyName = null;

    /**
     * @var int
     */
    public int $offset = 0;

    /**
     * @var int
     */
    public int $limit = -1;

    /**
     * @var string
     */
    public string $mvc = '';

    /**
     * @var boolean
     */
    public bool $query_pass = false;

    // Base for query
    function __call(string $fun, array $args): array
    {
        if ($fun === 'query_pass') {
            $this->query_pass = true;
            $fun = 'query';
        }
        $count = count($args);
        switch ($fun) {
            case 'query':
                switch ($count) {
                    case 0:
                        break;
                    case 1:
                        $this->sql = $args[0];
                        break;
                    case 2:
                        $this->sql = $args[0];
                        $this->data = $args[1];
                        break;
                    case 3:
                        $this->sql = $args[0];
                        $this->data = $args[1];
                        $this->keyName = $args[2];
                        break;
                    case 4:
                        $this->sql = $args[0];
                        $this->data = $args[1];
                        $this->keyName = $args[2];
                        $this->offset = $args[3];
                        break;
                    case 5:
                        $this->sql = $args[0];
                        $this->data = $args[1];
                        $this->keyName = $args[2];
                        $this->offset = $args[3];
                        $this->limit = $args[4];
                        break;
                    default:
                        die("【ERROR】Wrong parameters for \"$fun\".");
                }
                $result = OracleBase::query($this->modelType, $this->modelName, $this->tableName, $this->sql, $this->data, $this->data_bind, $this->keyName, $this->offset, $this->limit, $this->mvc, $this->query_pass);
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
        $this->query_pass = false;
    }

    /**
     * According to the given name as the return key.
     *
     * @param string|null $keyName
     *
     * @return object
     */
    public function keyName(?string $keyName): object
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
    public function offset(int $offset): object
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
    public function limit(int $limit): object
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
    public function createTable(): string
    {
        return Ddl::createTable($this->modelType, $this->modelName, $this->tableName, $this->mvc);
    }

    /**
     * Comment table.
     *
     * @return string
     */
    public function commentTable(): string
    {
        return Ddl::commentTable($this->modelType, $this->modelName, $this->tableName, $this->mvc);
    }

    // Dml

    /**
     * Insert data or Merge insert.
     *
     * @return object
     */
    public function insert(): object
    {
        empty($this->action) ? $this->action = 'INSERT' : null;
        if ($this->action === 'INSERT') {
            $this->sql = Dml::insert($this->modelType, $this->modelName, $this->tableName, $this->mvc);
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
    public function values(array $data = []): object
    {
        if ($this->action === 'INSERT') {
            $res = Dml::values($this->modelType, $this->modelName, $this->tableName, $data, [], $this->mvc);
            $this->sql .= $res['command'];
            $this->data = array_merge($this->data, $res['data']);
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeValues($this->modelType, $this->modelName, $this->tableName, $data, $this->mvc);
        }

        return $this;
    }

    /**
     * Delete data.
     *
     * @return object
     */
    public function delete(): object
    {
        empty($this->action) ? $this->action = 'DELETE' : null;
        if ($this->action === 'DELETE') {
            $this->sql = Dml::delete($this->modelType, $this->modelName, $this->tableName, $this->mvc);
        }

        return $this;
    }

    /**
     * Update data or Merge update.
     *
     * @return object
     */
    public function update(): object
    {
        empty($this->action) ? $this->action = 'UPDATE' : null;
        if ($this->action === 'UPDATE') {
            $this->sql = Dml::update($this->modelType, $this->modelName, $this->tableName, $this->mvc);
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
    public function set(array $data = []): object
    {
        if ($this->action === 'UPDATE') {
            $res = Dml::set($this->modelType, $this->modelName, $this->tableName, $data, [], $this->mvc);
            $this->sql .= $res['command'];
            $this->data = array_merge($this->data, $res['data']);
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeSet($this->modelName, $data, $this->mvc);
        }

        return $this;
    }

    /**
     * Merge table.
     *
     * @return object
     */
    public function merge(): object
    {
        empty($this->action) ? $this->action = 'MERGE' : null;
        $this->sql = Dml::merge($this->modelType, $this->modelName, $this->tableName, $this->mvc);

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
    public function using(string $user, string $tableName): object
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
    public function on(string $target, string $source): object
    {
        $this->sql .= Dml::on($target, $source);

        return $this;
    }

    /**
     * Merge matched.
     *
     * @return object
     */
    public function matched(): object
    {
        $this->sql .= Dml::matched();

        return $this;
    }

    /**
     * Merge not matched.
     *
     * @return object
     */
    public function not(): object
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
    public function select(array $data = []): object
    {
        empty($this->action) ? $this->action = 'SELECT' : null;
        $this->sql = Dql::select($this->modelType, $this->modelName, $this->tableName, $data, 0, $this->mvc);

        return $this;
    }

    /**
     * Select distinct data.
     *
     * @param array $data
     *
     * @return object
     */
    public function select_distinct(array $data = []): object
    {
        empty($this->action) ? $this->action = 'SELECT' : null;
        $this->sql = Dql::select($this->modelType, $this->modelName, $this->tableName, $data, 1, $this->mvc);

        return $this;
    }

    /**
     * Where.
     *
     * @param array $data
     *
     * @return object
     */
    public function where(array $data): object
    {
        $res = Dql::where($this->modelType, $this->modelName, $this->tableName, $data, [], $this->mvc);
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
    public function orderby(array $data): object
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
    public function groupby(array $data): object
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
        return Dcl::commit($this->modelType, $this->modelName, $this->mvc);
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        return Dcl::rollback($this->modelType, $this->modelName, $this->mvc);
    }

}