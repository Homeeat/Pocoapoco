<?php


namespace Ntch\Pocoapoco\Psr\Psr7;


use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{

    /**
     * HTTP server.
     *
     * @var array
     */
    private array $serverParams = [];

    /**
     * HTTP cookie.
     *
     * @var array
     */
    private array $cookieParams = [];

    /**
     * HTTP query.
     *
     * @var array
     */
    private array $queryParams = [];

    /**
     * HTTP body.
     *
     * @var object|array|string|null
     */
    protected $parsedBody;

    /**
     * HTTP attribute
     *
     * @var array
     */
    private array $attributes = [];

    /**
     * @inheritDoc
     */
    public function getServerParams(): array
    {
        $serverParams = $_SERVER;
        return $this->serverParams;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies)
    {
        $this->cookieParams = $cookies;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query)
    {
        $this->queryParams = $query;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles()
    {
        // TODO: Implement getUploadedFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        // TODO: Implement withUploadedFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data)
    {
        $this->parsedBody = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
        return $this;
    }
}