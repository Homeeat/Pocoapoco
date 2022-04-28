<?php

use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\Log\Base as LogBase;
use Ntch\Pocoapoco\WebRestful\Routing\Base as RoutingBase;
print "<pre>";
// Psr-4
require __DIR__ . '/../vendor/autoload.php';

// Error
new ErrorBase();

// Log
$log = new LogBase();
$log->logBase();

// Routing
$routerBase = new RoutingBase();
$routerBase->routerBase();

ErrorBase::triggerError("Router can't find a matching path." , 4, 0);
