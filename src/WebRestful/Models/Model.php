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

use Ntch\Pocoapoco\WebRestful\Models\Server;

class Model
{
    /**
     * @var string
     */
    public string $mvc = '';

    /**
     * @var string
     */
    public string $modelName = '';

    /**
     * @var string
     */
    public string $modelType = '';

    /**
     * @var string
     */
    public string $userName = '';

    /**
     * @var string
     */
    public string $schemaName = '';

    /**
     * @var string
     */
    public string $tableName = '';

    /**
     * @var Server
     */
    public Server $server;

    // Base for query
    public function __call(string $fun, array $args): array
    {
        if ($fun === 'query_pass') {
            $this->server->query_pass = true;
            $fun = 'query';
        }
        switch ($fun) {
            case 'query':
                $result = $this->server->query($this->mvc, $this->modelName, $this->tableName, $args);
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
     * According to the given name as the return key.
     *
     * @param string|null $keyName
     *
     * @return object
     */
    public function keyName(?string $keyName): object
    {
        $this->server->keyName($keyName);
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
        $this->server->top($count);
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
        $this->server->offset($offset);
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
        $this->server->fetch($limit);
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
        $this->server->limit($limit);
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
        return $this->server->createTable($this->mvc, $this->modelName, $this->schemaName, $this->userName, $this->tableName);
    }

    /**
     * Comment table.
     *
     * @return string
     */
    public function commentTable(): string
    {
        return $this->server->commentTable($this->mvc, $this->modelName, $this->schemaName, $this->userName, $this->tableName);
    }

    // Dml

    /**
     * Insert data or Merge insert.
     *
     * @return object
     */
    public function insert(): object
    {
        $this->server->insert($this->mvc, $this->modelName, $this->schemaName, $this->userName, $this->tableName);
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
        $this->server->values($this->mvc, $this->modelName, $this->tableName, $data);
        return $this;
    }

    /**
     * Delete data.
     *
     * @return object
     */
    public function delete(): object
    {
        $this->server->delete($this->mvc, $this->modelName, $this->schemaName, $this->userName, $this->tableName);
        return $this;
    }

    /**
     * Update data or Merge update.
     *
     * @return object
     */
    public function update(): object
    {
        $this->server->update($this->mvc, $this->modelName, $this->schemaName, $this->userName, $this->tableName);
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
        $this->server->set($this->mvc, $this->modelName, $this->tableName, $data);
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
        $this->server->select($this->mvc, $this->modelName, $this->schemaName, $this->userName, $this->tableName, $data);
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
        $this->server->select_distinct($this->mvc, $this->modelName, $this->schemaName, $this->userName, $this->tableName, $data);
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
        $this->server->where($this->mvc, $this->modelName, $this->tableName, $data);
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
        $this->server->orderby($data);
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
        $this->server->groupby($data);
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
        $this->server->commit();
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        $this->server->rollback();
    }
}