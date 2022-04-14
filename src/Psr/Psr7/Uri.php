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

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{

    // <scheme>://<user>:<password>@<host>:<port>/<path>;<params>?<query>#<fragment>

    /**
     * Valid Scheme.
     *
     * @var array
     */
    protected static array $validScheme = [
        'http' => 80,
        'https' => 443
    ];

    /**
     * The scheme component of the URI.
     *
     * @var string
     */
    protected string $scheme = '';

    /**
     * The host component of the URI.
     *
     * @var string
     */
    protected string $host = '';

    /**
     * The port component of the URI.
     *
     * @var null|int
     */
    protected $port;

    /**
     * The path component of the URI.
     *
     * @var string
     */
    protected string $path;

    /**
     * The query component of the URI.
     *
     * @var string
     */
    protected string $query;

    /**
     * The fragment component of the URI.
     *
     * @var string
     */
    protected string $fragment;

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        // TODO: Implement getAuthority() method.
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        // TODO: Implement getUserInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $scheme = str_replace('://', '', strtolower($scheme));

        foreach (self::$validScheme as $key => $value) {
            if ($key === $scheme) {
                $this->scheme = $key;
                return $this;
            }
        }

        die('【ERROR】Invalid Scheme. Uri scheme must be one of：'
            . implode(', ', array_keys(self::$validScheme)));
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        // TODO: Implement withUserInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        if(!is_string($host)) {
            die('【ERROR】Hort must be string.');
        }

        $host = strtolower($host);
        $this->host = $host;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        if (is_null($port) || (is_integer($port) && ($port >= 1 && $port <= 65535))) {
            $this->port = $port;
            return $this;
        }

        die('【ERROR】Port must be null or in a range of 0 ~ 65535.');
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        if(!is_string($path)) {
            die('【ERROR】Path must be string.');
        }

        $match = preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
        $this->path = $match;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        if(!is_string($query)) {
            die('【ERROR】Query must be string.');
        }

        $match = preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
        $this->query = $match;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        if(!is_string($fragment)) {
            die('【ERROR】Fragment must be string.');
        }

        $match = preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $fragment
        );
        $this->fragment = $match;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }
}