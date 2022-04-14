<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Http\Request;

use Ntch\Pocoapoco\Http\Psr7;

class Body extends Psr7
{

    /**
     * get Body information.
     *
     * @return \stdClass
     */
    public function getBody(): \stdClass
    {
        $body = new \stdClass();
        $body->input = $this->serverRequest->getParsedBody();
        return $body;
    }

    /**
     * Set Body information.
     *
     * @return void
     */
    public function setBody()
    {
        $this->setInput();
    }

    /**
     * Set body.
     *
     * @return void
     */
    private function setInput()
    {
        $input = @file_get_contents('php://input');
        $this->serverRequest->withParsedBody($input);
    }

}