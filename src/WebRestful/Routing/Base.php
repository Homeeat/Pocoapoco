<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Routing;

use Ntch\Pocoapoco\WebRestful\WebRestful;

class Base extends WebRestful
{

    /**
     * Router entry point.
     *
     * @return void
     */
    public function routerBase()
    {
        $this->webRestfulCheckList('router',null, null, 'router', null);
    }
    
}