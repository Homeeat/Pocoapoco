<?php


namespace Ntch\Pocoapoco\Psr\Psr3;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class LoggerAware implements LoggerAwareInterface
{

    /**
     * @inheritDoc
     */
    public function setLogger(LoggerInterface $logger): void
    {
        // TODO: Implement setLogger() method.
    }
}