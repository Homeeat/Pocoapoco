<?php

use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\Log\Base as LogBase;
use Ntch\Pocoapoco\WebRestful\Routing\Base as RoutingBase;
use Ntch\Pocoapoco\WebRestful\WebRestful;

// Psr-4
require __DIR__ . '/../../../autoload.php';

// WebRestful
$webRestful = new WebRestful();
$webRestful->setUuid();

// Error
new ErrorBase();

// Log
$log = new LogBase();
$log->logBase('');

// Routing
$routerBase = new RoutingBase();
$routerBase->routerBase();

ErrorBase::triggerError("Router can't find a matching path." , 4, 0);