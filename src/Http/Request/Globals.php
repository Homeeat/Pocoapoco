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

class Globals extends Psr7
{

    /**
     * @param array
     */
    public array $files = [];

    /**
     * get Globals information.
     *
     * @return \stdClass
     */
    public function getGlobals(): \stdClass
    {
        $globals = new \stdClass();
        $globals->cookie = $this->serverRequest->getCookieParams();
        $globals->query = $this->serverRequest->getQueryParams();
        $globals->attributes = $this->serverRequest->getAttributes();
        $globals->files = $this->getFiles();
        return $globals;
    }

    /**
     * Set Globals information.
     *
     * @return void
     */
    public function setGlobals()
    {
        $this->setCookie();
        $this->setQuery();
        $this->setAttribute();
        $this->setFiles();
    }

    /**
     * Set cookie with $_COOKIE.
     *
     * @return void
     */
    private function setCookie()
    {
        $cookies = $_COOKIE;
        $this->serverRequest->withCookieParams($cookies);
    }

    /**
     * Set query with $_GET.
     *
     * @return void
     */
    private function setQuery()
    {
        $query = $_GET;
        $this->serverRequest->withQueryParams($query);
    }

    /**
     * Set attribute with $_POST.
     *
     * @return void
     */
    private function setAttribute()
    {
        $attributes = $_POST;
        foreach ($attributes as $key => $value) {
            $this->serverRequest->withAttribute($key, $value);
        }
    }

    /**
     * Set files with $_FILES.
     *
     * @return void
     */
    private function setFiles()
    {
        $this->files = $_FILES;
    }

    /**
     * Get files.
     *
     * @return void
     */
    public function getFiles()
    {
        return $this->files;
    }

}