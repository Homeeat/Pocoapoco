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

class Server
{
    /**
     * @var string
     */
    public string $serverName = '';

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
     * @var boolean
     */
    public bool $query_pass = false;

    // Base for query
    public function query(string $mvc, string $modelName, string $tableName, array $args): array
    {
        $count = count($args);
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
                die("【ERROR】Wrong parameters for query.");
        }
        $result = $this->driverQuery($mvc, $modelName, $tableName);
        $this->clean();
        return $result;
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


}