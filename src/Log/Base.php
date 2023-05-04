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
        if(empty($log)) {
            $type = 'pocoapoco';
        }else{
            $type = 'project';
        }
        $this->setLogInfo($log, $type);
        $this->checkLogConfig($type);
        $this->setFileName($type);
    }

    /**
     * Set data from log.ini.
     *
     * @return void
     */
    private function setLogInfo(string $log, string $type)
    {
        if(!empty($log)){
            $setting = new SettingsBase();
            self::$log[$type] = $setting->getSettingData('log')[$log];
        }else{
            self::$log[$type] = [
                'folder' => $this->basePath . DIRECTORY_SEPARATOR . 'pocoapoco',
                'file' => 'logFile'
            ];
        }
    }

    /**
     * Check log.ini config.
     *
     * @return void
     */
    private function checkLogConfig(string $type)
    {
        //folder
        if(isset(self::$log[$type]['folder'])){
            if(substr(self::$log[$type]['folder'],0,1) !== DIRECTORY_SEPARATOR){
                die("【ERROR】Setting log.ini tag 'folder' use realpath.");
            }
            if(substr(self::$log[$type]['folder'], -1) === DIRECTORY_SEPARATOR){
                self::$log[$type]['folder'] = substr(self::$log[$type]['folder'],0,strlen(self::$log[$type]['folder'])-1);
            }
        }else{
            die("【ERROR】Setting log.ini tag 'folder' not found.");
        }

        //file
        if(isset(self::$log[$type]['file'])){
            if(strpos(self::$log[$type]['file'],DIRECTORY_SEPARATOR)){
                ErrorBase::triggerError('Not allowed log file ：'.self::$log[$type]['file'], 4, 0);
            }
        }else{
            die("【ERROR】Setting log.ini tag 'file' not found.");
        }
    }

    /**
     * Check folder for exists, if not create the folder.
     * According to log.ini tag 'cycle'
     *
     * @return void
     */
    private function setFileName(string $type)
    {
        $this->mkdirFolder(self::$log[$type]['folder']);
        $logDate = date('Y-m-d');
        self::$log[$type]['fileName'] = self::$log[$type]['folder'] . DIRECTORY_SEPARATOR . self::$log[$type]['file'] . '_' . $logDate . '.log';
    }

    private function mkdirFolder($folder)
    {
        if (!is_dir($folder)) {
            $pos = strrpos($folder, DIRECTORY_SEPARATOR);
            if(empty($pos)){
                $parentFolder = DIRECTORY_SEPARATOR;
            }else{
                $parentFolder = substr($folder, 0, $pos);
            }
            $this->mkdirFolder($parentFolder);
            if (is_writable($parentFolder)) {
                mkdir($folder, 0755);
            }else{
                ErrorBase::triggerError("Check permissions path：$parentFolder", 4, 0);
            }
        }
    }

    /**
     * Get log info.
     *
     * @param string $type
     * @return array
     */
    public function getLogInfo(string $type = 'project'): array
    {
        if(isset(self::$log[$type])){
            $log['fileName'] = self::$log[$type]['fileName'];
        }else{
            $log['fileName'] = null;
        }
        return $log;
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
     * @param string $type
     * @return void
     */
    public static function log(string $level, string $message, array $context, string $type = 'project')
    {
        $logger = new Logger(self::$log[$type]);

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