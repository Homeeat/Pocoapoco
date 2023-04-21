<?php

use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\WebRestful\Routing\Base as RoutingBase;

// Psr-4
require __DIR__ . '/../../../autoload.php';

// Error
new ErrorBase();

// Routing
$routerBase = new RoutingBase();
$routerBase->routerBase();

ErrorBase::triggerError("Router can't find a matching path." , 4, 0);
