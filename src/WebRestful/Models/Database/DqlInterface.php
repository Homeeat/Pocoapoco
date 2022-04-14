<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Models\Database;

interface DqlInterface
{

    /**
     * Select data.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     *
     * @return array
     */
    public static function select(string $modelType, string $modelName, string $tableName, array $data);
    
    /**
     * Where.
     *
     * @param string $modelType
     * @param string $modelName
     * @param string $tableName
     * @param array $data
     *
     * @return string
     */
    public static function where(string $modelType, string $modelName, string $tableName, array $data);

    /**
     * Order by.
     *
     * @param array $data
     *
     * @return string
     */
    public static function orderby(array $data);

    /**
     * Group by.
     *
     * @param array $data
     *
     * @return string
     */
    public static function groupby(array $data);

}