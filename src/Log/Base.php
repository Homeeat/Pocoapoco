<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Log;

use Ntch\Pocoapoco\WebRestful\WebRestful;
use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingsBase;
use Ntch\Pocoapoco\Psr\Psr3\Logger;

class Base extends WebRestful
{

    /**
     * @var array|null
     */
    protected static ?array $log;

    /**
     * Log entry point.
     *
     * @return void
     */
    public function logBase()
    {
        $this->webRestfulCheckList('log', null, null, null, null);
        $this->setLogInfo();
        $this->checkLogConfig();
        $this->mkdirFolder();
    }

    /**
     * Set data from log.ini.
     *
     * @return void
     */
    private function setLogInfo()
    {
        $setting = new SettingsBase();
        $setting->settingBase('/', 'log');
        self::$log = $setting->getSettingData('log');
    }

    /**
     * Check log.ini config.
     *
     * @return void
     */
    private function checkLogConfig()
    {
        $logConfigList = ['cycle'];
        $logCycleList = ['yearly', 'monthly', 'weekly', 'daily'];

        foreach ($logConfigList as $key) {
            isset(self::$log[$key]) ? null : die("【ERROR】Setting log.ini tag \"$key\" is not exist.");
        }

        in_array(self::$log['cycle'], $logCycleList) ? null : die("【ERROR】Setting log.ini tag 'cycle' value is not support.");
    }

    /**
     * Check folder is exist, if not create the folder.
     * According to log.ini tag 'cycle'
     *
     * @return void
     */
    private function mkdirFolder()
    {
        $logPath = $this->basePath;

        if (is_writable($logPath)) {
            $year = date('Y');
            $month = date('m');
            $week = date('W');
            $day = date('d');
            $yearPath = $logPath . DIRECTORY_SEPARATOR . $year;
            $monthPath = $logPath . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month;

            switch (self::$log['cycle']) {
                case 'yearly':
                    self::$log['fileName'] = $logPath . DIRECTORY_SEPARATOR . 'Y' . $year . '.log';
                    break;
                case 'monthly':
                    self::$log['fileName'] = $yearPath . DIRECTORY_SEPARATOR . 'M' . $month . '.log';
                    if (!is_dir($yearPath)) {
                        mkdir($yearPath, 0777);
                    }
                    break;
                case 'weekly':
                    self::$log['fileName'] = $yearPath . DIRECTORY_SEPARATOR . 'W' . $week . '.log';
                    if (!is_dir($yearPath)) {
                        mkdir($yearPath, 0777);
                    }
                    break;
                case 'daily':
                    self::$log['fileName'] = $monthPath . DIRECTORY_SEPARATOR . 'D' . $day . '.log';
                    if (!is_dir($yearPath)) {
                        mkdir($yearPath, 0777);
                        if (!is_dir($monthPath)) {
                            mkdir($monthPath, 0777);
                        }
                    }
                    break;
            }
        } else {
            ErrorBase::triggerError("Check permissions path：$logPath", 4, 0);
        }

    }

    /**
     * Write log.
     * 【 type 】
     * - EMERGENCY => system is unusable.
     * - ALERT => action must be taken immediately.
     * - CRITICAL => critical conditions.
     * - ERROR => error conditions.
     * - WARNING => warning conditions.
     * - NOTICE => normal, but significant, condition.
     * - INFO => informational message.
     * - DEBUG => debug-level message.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function log(string $level, string $message, array $context)
    {
        $logger = new Logger(self::$log);

        switch (strtoupper($level)) {
            case 'EMERGENCY':
                $logger->emergency($message, $context);
                break;
            case 'ALERT':
                $logger->alert($message, $context);
                break;
            case 'CRITICAL':
                $logger->critical($message, $context);
                break;
            case 'ERROR':
                $logger->error($message, $context);
                break;
            case 'WARNING':
                $logger->warning($message, $context);
                break;
            case 'NOTICE':
                $logger->notice($message, $context);
                break;
            case 'INFO':
                $logger->info($message, $context);
                break;
            case 'DEBUG':
                $logger->debug($message, $context);
                break;
            default:
                $logger->log($level, $message, $context);
                break;
        }

    }

}