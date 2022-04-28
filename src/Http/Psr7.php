<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Http;

use Ntch\Pocoapoco\Psr\Psr7\Message;
use Ntch\Pocoapoco\Psr\Psr7\Request;
use Ntch\Pocoapoco\Psr\Psr7\Response;
use Ntch\Pocoapoco\Psr\Psr7\ServerRequest;
use Ntch\Pocoapoco\Psr\Psr7\Stream;
use Ntch\Pocoapoco\Psr\Psr7\UploadedFile;
use Ntch\Pocoapoco\Psr\Psr7\Uri;

class Psr7
{

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var ServerRequest
     */
    protected $serverRequest;

    /**
     * @var Stream
     */
    protected $stream;

    /**
     * @var UploadedFile
     */
    protected $uploadedFile;

    /**
     * @var Uri
     */
    public $uri;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->message = new Message();
        $this->request = new Request();
        $this->response = new Response();
        $this->serverRequest = new ServerRequest();
        $this->stream = new Stream();
        $this->uploadedFile = new UploadedFile();
        $this->uri = new Uri();
    }

}