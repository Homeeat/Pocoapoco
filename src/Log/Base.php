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
    public function logBase(string $log)
    {
        $this->webRestfulCheckList('log', null, null, null, null);
        $this->setLogInfo($log);
        $this->checkLogConfig();
        $this->setFileName();
    }

    /**
     * Set data from log.ini.
     *
     * @return void
     */
    private function setLogInfo(string $log)
    {
        if(!empty($log)){
            $setting = new SettingsBase();
            self::$log = $setting->getSettingData('log')[$log];
        }else{
            self::$log = [
                'folder' => DIRECTORY_SEPARATOR . 'pocoapoco'
            ];
        }
    }

    /**
     * Check log.ini config.
     *
     * @return void
     */
    private function checkLogConfig()
    {
        //folder
        if(isset(self::$log['folder'])){
            if(substr(self::$log['folder'], -1) === DIRECTORY_SEPARATOR){
                self::$log['folder'] = substr(self::$log['folder'],0,strlen(self::$log['folder'])-1);
            }
            if(substr(self::$log['folder'],0,1) !== DIRECTORY_SEPARATOR){
                self::$log['folder'] = DIRECTORY_SEPARATOR . self::$log['folder'];
            }
        }else{
            self::$log['folder'] = DIRECTORY_SEPARATOR;
        }

        //file
        if(isset(self::$log['file'])){
            if(strpos(self::$log['file'],DIRECTORY_SEPARATOR)){
                ErrorBase::triggerError('Not allowed log file ：'.self::$log['file'], 4, 0);
            }
        }else{
            self::$log['file'] = 'logFile';
        }
    }

    /**
     * Check folder for exists, if not create the folder.
     * According to log.ini tag 'cycle'
     *
     * @return void
     */
    private function setFileName()
    {
        if (is_writable($this->basePath)) {
            if(self::$log['folder'] !== DIRECTORY_SEPARATOR){
                $logPath = $this->basePath.self::$log['folder'];
            }else{
                $logPath = $this->basePath;
            }
            $this->mkdirFolder($logPath);
            $logDate = date('Y-m-d');
            self::$log['fileName'] = $logPath . DIRECTORY_SEPARATOR . self::$log['file'] . '_' . $logDate . '.log';
        } else {
            ErrorBase::triggerError("Check permissions path：$this->basePath", 4, 0);
        }

    }

    private function mkdirFolder($folder)
    {
        if (!is_dir($folder)) {
            $pos = strrpos($folder, DIRECTORY_SEPARATOR);
            $parentFolder = substr($folder, 0, $pos);
            $this->mkdirFolder($parentFolder);
            mkdir($folder, 0777);
        }
    }

    /**
     * Get log info.
     *
     * @return array
     */
    public function getLogInfo(): array
    {
        return ['fileName' => self::$log['fileName']];
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