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

use Ntch\Pocoapoco\WebRestful\Models\Database\Mssql\Base as MssqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mssql\Ddl;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mssql\Dml;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mssql\Dql;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mssql\Dcl;

class MssqlModel
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

    // Base for query
    function __call(string $fun, array $args): array
    {
        $count = count($args);
        switch ($fun) {
            case 'query':
                switch ($count) {
                    case 0:
                        $result = MssqlBase::query($this->modelType, $this->modelName, $this->tableName, $this->sql, $this->data, $this->data_bind, $this->keyName, $this->offset, $this->limit);
                        break;
                    case 1:
                        $result = MssqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $sqlBind = null, $this->data_bind, $this->keyName, $this->offset, $limit = -1);
                        break;
                    case 2:
                        $result = MssqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $this->data_bind, $this->keyName, $this->offset, $limit = -1);
                        break;
                    case 3:
                        $result = MssqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $this->data_bind, $args[2], $this->offset, $limit = -1);
                        break;
                    case 4:
                        $result = MssqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $this->data_bind, $args[2], $args[3], $limit = -1);
                        break;
                    case 5:
                        $result = MssqlBase::query($this->modelType, $this->modelName, $this->tableName, $args[0], $args[1], $this->data_bind, $args[2], $args[3], $args[4]);
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
     * Only search for a specific number.
     *
     * @param int $count
     *
     * @return object
     */
    public function top(int $count): object
    {
        $this->sql = str_replace('SELECT', "SELECT TOP $count", $this->sql);
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
        $this->sql .= "\nOFFSET $offset Rows";
        return $this;
    }

    /**
     * Only search for a specific number.
     *
     * @param int $limit
     *
     * @return object
     */
    public function fetch(int $limit): object
    {
        $this->sql .= "\nFetch Next $limit Rows Only";
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
        return Ddl::createTable($this->modelType, $this->modelName, $this->tableName);
    }

    /**
     * Comment table.
     *
     * @return string
     */
    public function commentTable(): string
    {
        return Ddl::commentTable($this->modelType, $this->modelName, $this->tableName);
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
    public function value(array $data = []): object
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
    public function delete(): object
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
    public function update(): object
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
    public function set(array $data = []): object
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
    public function select(array $data = []): object
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
    public function where(array $data): object
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