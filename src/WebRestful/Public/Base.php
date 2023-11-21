<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Public;

use Ntch\Pocoapoco\WebRestful\WebRestful;

class Base extends WebRestful
{

    /**
     * Public entry point.
     *
     * @param string $path
     * @param string $class
     *
     * @return void
     */
    public function publicBase(string $path, string $class)
    {
        $isWebRestfulPass = $this->webRestfulCheckList('public', null, $path, $class, null);
        if($isWebRestfulPass) {
            $this->publicExecute();
        }
    }

    /**
     * Execute public.
     *
     * @return void
     */
    private function publicExecute()
    {
        new Show($this->absoluteFile, self::$uuid);
        exit();
    }

}