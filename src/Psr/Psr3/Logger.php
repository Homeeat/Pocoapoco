<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Psr\Psr3;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{

    use \Ntch\Pocoapoco\Tools\Output;

    /**
     * @var array
     */
    protected array $log = [];

    /**
     * Construct
     */
    public function __construct($log)
    {
        $this->log = $log;
    }

    /**
     * @inheritDoc
     */
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->write('EMERGENCY', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->write('ALERT', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->write('CRITICAL', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->write('NOTICE', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->write('DEBUG', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->write($level, $message, $context);
    }

    /**
     * Write log.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function write(mixed $level, \Stringable|string $message, array $context)
    {
        $date = $context['date'];
        $time = $context['time'];
        unset($context['date']);
        unset($context['time']);
        $context_json = $this->arrayToJson($context);
        $log = "[$date $time] $level - $message\n$context_json\n";

        file_put_contents($this->log['fileName'], $log, FILE_APPEND);
    }

}