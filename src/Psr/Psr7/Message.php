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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    use Header;

    /**
     * A HTTP protocol version number.
     *
     * @var string
     */
    protected string $protocolVersion;

    /**
     * Valid HTTP version numbers.
     *
     * @var array
     */
    protected static array $validProtocolVersions = [
        '1.0',
        '1.1',
        '2.0',
        '3.0',
    ];

    /**
     * An array of mapping header information.
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * @var StreamInterface
     */
    protected StreamInterface $body;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        foreach (self::$validProtocolVersions as $key => $value) {
            if ($value === $version) {
                $this->protocolVersion = $value;
                return $this;
            }
        }

        die('【ERROR】Invalid HTTP version. Only supports version：'
            . implode(', ', array_values(self::$validProtocolVersions)));
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        $name = strtolower(trim($name));

        return isset($this->headers[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        $name = strtolower(trim($name));

        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $isName = $this->normalizationHeaderName($name);
        $isValue = $this->normalizationHeaderValue($value);

        if($isName && $isValue) {
            $name = strtolower(trim($name));
            $value = strtolower(trim($value));
            $this->headers[$name] = $value;
        } elseif (!$isName){
            die('【ERROR】Header name 【' . $name . '】must be an RFC 7230 compatible string.');
        } elseif (!$isValue) {
            die('【ERROR】Header value 【' . $value . '】 must be an RFC 7230 compatible string.');
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        $isName = $this->normalizationHeaderName($name);
        $isValue = $this->normalizationHeaderValue($value);

        if($isName && $isValue) {
            $name = strtolower(trim($name));
            $value = strtolower(trim($value));
            if( isset($this->headers[$name]) ) {
                $this->headers[$name] = array_merge($this->headers[$name], $value);
            } else {
                $this->headers[$name] = $value;
            }
        } elseif (!$isName){
            die('【ERROR】Header name 【' . $name . '】must be an RFC 7230 compatible string.');
        } elseif (!$isValue) {
            die('【ERROR】Header value 【' . $value . '】 must be an RFC 7230 compatible string.');
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        $name = strtolower(trim($name));

        if( isset($this->headers[$name]) ) {
            unset($this->headers[$name]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        $this->body = $body;

        return $this;
    }
}
