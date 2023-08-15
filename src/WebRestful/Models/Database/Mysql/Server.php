<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see           https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license       https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database\Mysql;

use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Base as MysqlBase;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Ddl;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Dml;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Dql;
use Ntch\Pocoapoco\WebRestful\Models\Database\Mysql\Dcl;
use Ntch\Pocoapoco\WebRestful\Models\Server as ModelServer;

class Server extends ModelServer
{
    protected function driverQuery(string $mvc, string $modelName, string $tableName)
    {
        return MysqlBase::query($this->serverName, $mvc, $modelName, $tableName, $this->sql, $this->data, $this->data_bind, $this->keyName, $this->offset, $this->limit, $this->query_pass);
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
            $sql = str_replace(":$key", "?", $sql);
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
        return '';
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
        $this->sql = Dml::insert($mvc, $modelName, $schemaName, $userName, $tableName);

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
        $res = Dml::values($mvc, $modelName, $tableName, $data, $this->data_bind);
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
    public function delete(string $mvc, string $modelName, ?string $schemaName, string $userName, string $tableName): object
    {
        empty($this->action) ? $this->action = 'DELETE' : null;
        $this->sql = Dml::delete($mvc, $modelName, $schemaName, $userName, $tableName);

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
        $this->sql = Dml::update($mvc, $modelName, $schemaName, $userName, $tableName);

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
        $res = Dml::set($mvc, $modelName, $tableName, $data, $this->data_bind);
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
        Dcl::commit($this->serverName);
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        Dcl::rollback($this->serverName);
    }

}