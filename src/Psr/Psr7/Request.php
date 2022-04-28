<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Psr\Psr7;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{

    /**
     * @var UriInterface
     */
    protected UriInterface $uri;

    /**
     * The target URL of the outgoing request.
     *
     * @var string
     */
    protected string $requestTarget;

    /**
     * The HTTP method of the outgoing request.
     *
     * @var string
     */
    protected string $method;

    /**
     * Valid HTTP method.
     *
     * @var array
     */
    protected static array $validMethods = [
//        'HEAD',
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
//        'CONNECT',
//        'OPTIONS',
//        'TRACE'
    ];

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $uriTarget = $this->uri->getPath();
        if ($uriTarget === '') {
            $uriTarget = '/';
        }
        if ($this->uri->getQuery() != '') {
            $uriTarget .= '?' . $this->uri->getQuery();
        }

        return $uriTarget;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('/\s/', $requestTarget)) {
            die('【ERROR】A request target cannot contain any whitespace.');
        }

        $this->requestTarget = $requestTarget;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method)
    {
        $method = strtoupper($method);

        foreach (self::$validMethods as $key => $value) {
            if ($value === $method) {
                $this->method = $method;
                return $this;
            }
        }

        die('【ERROR】Invalid HTTP method. Only supports：'
            . implode(', ', array_values(self::$validMethods)));
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $host = $uri->getHost();
        if ($host == '') {
            return;
        }

        $this->uri = $uri;
        if ($uri === $host) {
            return $this;
        }
    }

}