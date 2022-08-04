<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Controllers;

use Ntch\Pocoapoco\WebRestful\WebRestful;

class Base extends WebRestful
{

    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * Controller entry point.
     *
     * @param string $uri
     * @param string $path
     * @param string $class
     * @param string $method
     *
     * @return void
     */
    public function controllerBase(string $uri, string $path, string $class, string $method)
    {
        $isWebRestfulPass = $this->webRestfulCheckList('controller', $uri, $path, $class, $method);
        if ($isWebRestfulPass) {
            $this->controllerExecute($class, $method);
        }
    }

    /**
     * Execute controller method.
     *
     * @param string $class
     * @param string $method
     *
     * @return void
     */
    private function controllerExecute(string $class, string $method = 'index')
    {
        $controller = new $class();
        $controller->$method();
        exit();
    }

    /**
     * Get UUID.
     *
     * @return string
     */
    public function getUuid(): string
    {
        return self::uuid();
    }

    /**
     * Get Url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->nginx->uri['url'];
    }

    /**
     * Get Headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = $this->header->headers;
        $ignore = ['cookie', 'authorization'];
        foreach ($ignore as $name) {
            unset($headers[$name]);
        }

        return $headers;
    }

    /**
     * Get authorization.
     *
     * @return null|array
     */
    public function getAuthorization(): ?array
    {
        return $this->nginx->headers['authorization'];
    }

    /**
     * Get cookie variable.
     *
     * @return array
     */
    public function getCookie(): array
    {
        return $this->globals->cookie;
    }

    /**
     * Get request method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->nginx->request['method'];
    }

    /**
     * Get uri variable.
     *
     * @return array
     */
    public function getUri(): array
    {
        return self::$uriVariable;
    }

    /**
     * Get query.
     *
     * @return array
     */
    public function getQuery(): array
    {
        return $this->globals->query;
    }

    /**
     * Get input data.
     * If input data is json or xml then transform to array.
     *
     * @return null|string|array
     */
    public function getInput(): array|string|null
    {
        return $this->body->input;
    }

    /**
     * Get attribute.
     *
     * @return array
     */
    public function getAttribute(): array
    {
        return $this->globals->attributes;
    }

    /**
     * Get client ip.
     *
     * @return string
     */
    public function getUriHost(): string
    {
        return $this->nginx->uri['host'];
    }

    /**
     * Get client port.
     *
     * @return string
     */
    public function getUriPort(): string
    {
        return $this->nginx->uri['port'];
    }

    /**
     * Get request time (microsecond).
     *
     * @return array
     */
    public function getTime(): array
    {
        $unix = $this->nginx->request['time'];
        $time['unix'] = $unix;
        $time['date'] = date('Y-m-d', floor($unix));
        $time['time'] = date('H:i:s', floor($unix));
        return $time;
    }

    /**
     * Get files.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->globals->files;
    }

}