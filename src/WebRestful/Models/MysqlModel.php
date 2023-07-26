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
                        $this->sqlDataBind($this->sql, $this->data);
                        break;
                    case 3:
                        $this->sql = $args[0];
                        $this->data = $args[1];
                        $this->keyName = $args[2];
                        $this->sqlDataBind($this->sql, $this->data);
                        break;
                    case 4:
                        $this->sql = $args[0];
                        $this->data = $args[1];
                        $this->keyName = $args[2];
                        $this->offset = $args[3];
                        $this->sqlDataBind($this->sql, $this->data);
                        break;
                    case 5:
                        $this->sql = $args[0];
                        $this->data = $args[1];
                        $this->keyName = $args[2];
                        $this->offset = $args[3];
                        $this->limit = $args[4];
                        $this->sqlDataBind($this->sql, $this->data);
                        break;
                    default:
                        die("【ERROR】Wrong parameters for \"$fun\".");
                }
                $result = MysqlBase::query($this->modelType, $this->modelName, $this->tableName, $this->sql, $this->data, $this->data_bind, $this->keyName, $this->offset, $this->limit, $this->mvc, $this->query_pass);
                $this->clean();
                return $result;
            default:
                die("【ERROR】Not support \"$fun\"");
        }
    }

    /**
     * Data bind.
     *
     * @param array $data
     *
     * @return array
     */
    public function dataBind(array $data)
    {
        $bind = [];
        foreach ($data as $key => $value) {
            if (isset($this->schema[$key])) {
                $bind[$key] = $value;
            }
        }
        return $bind;
    }

    /**
     * Sql data bind.
     *
     * @param string $sql
     * @param array $data
     *
     * @return boolean
     */
    private function sqlDataBind(string $sql, array $data)
    {
        $bind_flag = 0;
        $this->data = array();
        foreach ($data as $key => $value) {
            $sql = str_replace(":$key", "?", $sql);
            $this->data[$bind_flag] = $key;
            $this->data_bind[$bind_flag] = $value;
            $bind_flag++;
        }
        $this->sql = $sql;
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
        $this->data_bind = [];
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
    public function limit(int $limit): object
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
    public function createTable(): string
    {
        return Ddl::createTable($this->modelType, $this->modelName, $this->tableName, $this->mvc);
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
        $this->sql = Dml::insert($this->modelType, $this->modelName, $this->tableName, $this->mvc);

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
        $res = Dml::values($this->modelType, $this->modelName, $this->tableName, $data, $this->data_bind, $this->mvc);
        $this->sql .= $res['command'];
        $this->data = array_merge($this->data, $res['data']);
        $this->data_bind = $res['data_bind'];

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
        $this->sql = Dml::delete($this->modelType, $this->modelName, $this->tableName, $this->mvc);

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
        $this->sql = Dml::update($this->modelType, $this->modelName, $this->tableName, $this->mvc);

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
        $res = Dml::set($this->modelType, $this->modelName, $this->tableName, $data, $this->data_bind, $this->mvc);
        $this->sql .= $res['command'];
        $this->data = array_merge($this->data, $res['data']);
        $this->data_bind = $res['data_bind'];

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
        $res = Dql::where($this->modelType, $this->modelName, $this->tableName, $data, $this->data_bind, $this->mvc);
        $this->sql .= $res['command'];
        $this->data = array_merge($this->data, $res['data']);
        $this->data_bind = $res['data_bind'];

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