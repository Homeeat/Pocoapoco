<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Oracle;

use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Base as OracleBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Ddl;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Dml;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Dql;
use Ntch\Pocoapoco\WebRestful\Models\Database\Oracle\Dcl;
use Ntch\Pocoapoco\WebRestful\Models\Server as ModelServer;

class Server extends ModelServer
{
    protected function driverQuery(string $mvc, string $modelName, string $tableName)
    {
        return OracleBase::query($this->serverName, $mvc, $modelName, $tableName, $this->sql, $this->data, $this->data_bind, $this->keyName, $this->offset, $this->limit, $this->query_pass);
    }

    /**
     * Sql data bind.
     *
     * @param string $sql
     * @param array $data
     *
     * @return boolean
     */
    protected function sqlDataBind(string $sql, array $data)
    {
        $bind_flag = 0;
        $this->data = array();
        foreach ($data as $key => $value) {
            $sql = str_replace(":$key", ":$key$bind_flag", $sql);
            $this->data[$bind_flag] = $key;
            $this->data_bind[$bind_flag] = $value;
            $bind_flag++;
        }
        $this->sql = $sql;
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
    public function createTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName): string
    {
        return Ddl::createTable($mvc, $modelName, $schemaName, $userName, $tableName);
    }

    /**
     * Comment table.
     *
     * @return string
     */
    public function commentTable(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName): string
    {
        return Ddl::commentTable($mvc, $modelName, $schemaName, $userName, $tableName);
    }

    // Dml

    /**
     * Insert data or Merge insert.
     *
     * @return object
     */
    public function insert(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName): object
    {
        empty($this->action) ? $this->action = 'INSERT' : null;
        if ($this->action === 'INSERT') {
            $this->sql = Dml::insert($mvc, $modelName, $schemaName, $userName, $tableName);
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
    public function values(string $mvc, string $modelName, string $tableName, array $data = []): object
    {
        if ($this->action === 'INSERT') {
            $res = Dml::values($mvc, $modelName, $tableName, $data, $this->data_bind);
            $this->sql .= $res['command'];
            $this->data = array_merge($this->data, $res['data']);
            $this->data_bind = $res['data_bind'];
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeValues($mvc, $modelName, $tableName, $data);
        }

        return $this;
    }

    /**
     * Delete data.
     *
     * @return object
     */
    public function delete(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName): object
    {
        empty($this->action) ? $this->action = 'DELETE' : null;
        if ($this->action === 'DELETE') {
            $this->sql = Dml::delete($mvc, $modelName, $schemaName, $userName, $tableName);
        }

        return $this;
    }

    /**
     * Update data or Merge update.
     *
     * @return object
     */
    public function update(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName): object
    {
        empty($this->action) ? $this->action = 'UPDATE' : null;
        if ($this->action === 'UPDATE') {
            $this->sql = Dml::update($mvc, $modelName, $schemaName, $userName, $tableName);
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
    public function set(string $mvc, string $modelName, string $tableName, array $data = []): object
    {
        if ($this->action === 'UPDATE') {
            $res = Dml::set($mvc, $modelName, $tableName, $data, $this->data_bind);
            $this->sql .= $res['command'];
            $this->data = array_merge($this->data, $res['data']);
            $this->data_bind = $res['data_bind'];
        } elseif ($this->action === 'MERGE') {
            $this->sql .= Dml::mergeSet($mvc, $modelName, $data);
        }

        return $this;
    }

    /**
     * Merge table.
     *
     * @return object
     */
    public function merge(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName): object
    {
        empty($this->action) ? $this->action = 'MERGE' : null;
        $this->sql = Dml::merge($mvc, $modelName, $schemaName, $userName, $tableName);

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
    public function select(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName, array $data = []): object
    {
        empty($this->action) ? $this->action = 'SELECT' : null;
        $this->sql = Dql::select($mvc, $modelName, $schemaName, $userName, $tableName, $data, 0);

        return $this;
    }

    /**
     * Select distinct data.
     *
     * @param array $data
     *
     * @return object
     */
    public function select_distinct(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName, array $data = []): object
    {
        empty($this->action) ? $this->action = 'SELECT' : null;
        $this->sql = Dql::select($mvc, $modelName, $schemaName, $userName, $tableName, $data, 1);

        return $this;
    }

    /**
     * Where.
     *
     * @param array $data
     *
     * @return object
     */
    public function where(string $mvc, string $modelName, string $tableName, array $data): object
    {
        $res = Dql::where($mvc, $modelName, $tableName, $data, $this->data_bind);
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
        return Dcl::commit($this->serverName);
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        return Dcl::rollback($this->serverName);
    }

}