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

use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected int $code;

    /**
     * HTTP status reason phrase.
     *
     * @var string
     */
    protected string $reasonPhrase;

    /**
     * HTTP status codes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc2616
     *
     * @var array
     */
    protected static array $statusCode = [
        // 1xx => Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        // 2xx => Successful
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // 3xx => Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        // 4xx => Client Error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        // 5xx => Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
    ];

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        if(!is_integer($code)) {
            die('【ERROR】HTTP status code must be integer.');
        }

        foreach (self::$statusCode as $key => $value) {
            if ($key === $code) {
                $this->code = $key;
                $this->reasonPhrase = $value;
                return $this;
            }
        }

        die('【ERROR】Invalid HTTP status code. Only supports：'
            . implode(', ', array_keys(self::$statusCode)));
    }
}