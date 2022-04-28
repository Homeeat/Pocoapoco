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

class Header extends Psr7
{

    /**
     * get Globals information.
     *
     * @return \stdClass
     */
    public function getHeader(): \stdClass
    {
        $header = new \stdClass();
        $header->headers = $this->message->getHeaders();
        return $header;
    }

    /**
     * Set Header.
     *
     * @return void
     */
    public function setHeaders()
    {
        foreach (getallheaders() as $name => $value) {
            $this->message->withHeader($name, $value);
        }
    }

}