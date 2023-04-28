<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Error;

use Ntch\Pocoapoco\WebRestful\Routing\Router;
use Ntch\Pocoapoco\Log\Base as LogBase;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingsBase;
use Ntch\Pocoapoco\Mail\Mail;

class Base
{
    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * @var array|null
     */
    private static ?array $error;

    /**
     * @var int
     */
    private static int $errhttp = 4;

    /**
     * @var bool
     */
    private static bool $sendmail = true;

    /**
     * @var string
     */
    private static string $uuid = '';

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setErrorInfo();
        $this->checkErrorConfig();

        @set_error_handler([$this, 'setErrorHandler']);
        @set_exception_handler([$this, 'setExceptionHandler']);
        @register_shutdown_function([$this, 'registerShutdownFunction']);
    }

    /**
     * Set data from error.ini.
     *
     * @return void
     */
    public function setErrorInfo()
    {
        $setting = new SettingsBase();
        $setting->settingBase('error');
        self::$error = $setting->getSettingData('error');
    }

    /**
     * Check error.ini config.
     *
     * @return void
     */
    public function checkErrorConfig()
    {
        $errorConfigList = ['debug', 'page_4xx', 'page_5xx', 'mail_from', 'mail_to', 'mail_server'];

        foreach ($errorConfigList as $key) {
            isset(self::$error['MAIN'][$key]) ? null : die("【ERROR】Setting error.ini tag \"$key\" is not exist.");;
        }
    }

    /**
     * Trigger error.
     *
     * @param string $message
     * @param int $errhttp
     * @param int $sendmail
     * @param string $uuid
     *
     * @return void
     */
    public static function triggerError(string $message, int $errhttp, int $sendmail, string $uuid = '')
    {
        self::$errhttp = $errhttp;
        self::$sendmail = (boolean)$sendmail;
        self::$uuid = $uuid;
        trigger_error($message);
    }

    /**
     * Set error handler.
     *
     * @return void
     */
    public function registerShutdownFunction()
    {
        $errorInfo = error_get_last();
        isset($errorInfo) ? $this->setErrorHandler($errorInfo['type'], $errorInfo['message'], $errorInfo['file'], $errorInfo['line']) : null;
    }

    /**
     * Set exception handler.
     *
     * @param object $exception
     *
     * @return void
     */
    public function setExceptionHandler(object $exception)
    {
        isset($exception) ? $this->setErrorHandler($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine()) : null;
    }

    /**
     * Set error handler.
     *
     * @param int $no
     * @param string $message
     * @param string $file
     * @param int $line
     *
     * @return void
     */
    public function setErrorHandler(int $no, string $message, string $file, int $line)
    {
        $level = $this->errorLevel($no);
        $error_data = ['error' => "<pre><b>Code：</b>$no<br><b>Level：</b>$level<br><b>File：</b>$file<br><b>Line：</b>$line<br><b>Message：</b>$message<br>"];

        if ((boolean)self::$error['MAIN']['debug']) {

            $view = __DIR__ . '/View.php';
            @ob_end_clean();
            $this->data = $error_data;
            include($view);
            exit();

        } else {
            $router = new Router();
            LogBase::log($level, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8), 'pocoapoco');

            // MAIL
            (boolean)$ignore = $this->ignoreLevel('MAIL', $level);
            if ($ignore && self::$sendmail) {
                $router->mail([self::$error['MAIN']['mail_server']], 'error');
                $server = self::$error['MAIN']['mail_server'];
                $mail = new Mail($server, 'error');

                $fromInfo = explode(':', self::$error['MAIN']['mail_from']);
                $address = trim($fromInfo[0]);
                $name = trim($fromInfo[1]);
                $from[$address] = $name;

                $toList = explode(',', self::$error['MAIN']['mail_to']);
                $to = [];
                foreach ($toList as $list) {
                    $toInfo = explode(':', $list);
                    $address = trim($toInfo[0]);
                    $name = trim($toInfo[1]);
                    $to[$address] = $name;
                }

                $mail_view = __DIR__ . '/Mail.php';
                $mail_logo = __DIR__ . '/../Image/Pocoapoco_black.png';

                $mail->from($from)->to($to)->
                header(['POCOAPOCO' => self::$uuid])->
                subject('【 ERROR MESSAGE 】')->
                content('local', 'html', $mail_view, $error_data)->
                image($mail_logo, 'Pocoapoco_black', 'Pocoapoco_black.png')->
                send();
                
            }

            // PAGE
            (boolean)$ignore = $this->ignoreLevel('PAGE', $level);
            if ($ignore) {
                switch (self::$errhttp) {
                    case 4:
                        $http4xx = explode('/', self::$error['MAIN']['page_4xx']);
                        $class = end($http4xx);
                        $path = str_replace($class, '', self::$error['MAIN']['page_4xx']);
                        ob_end_clean();
                        $router->public($path, $class);
                        break;
                    case 5:
                        $http5xx = explode('/', self::$error['MAIN']['page_5xx']);
                        $class = end($http5xx);
                        $path = str_replace($class, '', self::$error['MAIN']['page_5xx']);
                        ob_end_clean();
                        $router->public($path, $class);
                        break;
                }
                exit();
            }
        }
    }

    /**
     * Error levels is ignore.
     *
     * @param string $type
     * @param string $level
     *
     * @return bool
     */
    private function ignoreLevel(string $type, string $level): bool
    {
        $ignore = false;
        switch ($level) {
            case 'E_EXCEPTION':
                $ignore = self::$error[$type]['E_EXCEPTION'];
                break;
            case 'E_ERROR':
                $ignore = self::$error[$type]['E_ERROR'];
                break;
            case 'E_WARNING':
                $ignore = self::$error[$type]['E_WARNING'];
                break;
            case 'E_PARSE':
                $ignore = self::$error[$type]['E_PARSE'];
                break;
            case 'E_NOTICE':
                $ignore = self::$error[$type]['E_NOTICE'];
                break;
            case 'E_CORE_ERROR':
                $ignore = self::$error[$type]['E_CORE_ERROR'];
                break;
            case 'E_CORE_WARNING':
                $ignore = self::$error[$type]['E_CORE_WARNING'];
                break;
            case 'E_COMPILE_ERROR':
                $ignore = self::$error[$type]['E_COMPILE_ERROR'];
                break;
            case 'E_COMPILE_WARNING':
                $ignore = self::$error[$type]['E_COMPILE_WARNING'];
                break;
            case 'E_USER_ERROR':
                $ignore = self::$error[$type]['E_USER_ERROR'];
                break;
            case 'E_STRICT':
                $ignore = self::$error[$type]['E_STRICT'];
                break;
            case 'E_RECOVERABLE_ERROR':
                $ignore = self::$error[$type]['E_RECOVERABLE_ERROR'];
                break;
            case 'E_DEPRECATED':
                $ignore = self::$error[$type]['E_DEPRECATED'];
                break;
            case 'E_USER_DEPRECATED':
                $ignore = self::$error[$type]['E_USER_DEPRECATED'];
                break;
            case 'E_ALL':
                $ignore = self::$error[$type]['E_ALL'];
                break;
        }
        return $ignore;
    }

    /**
     * Error levels.
     *
     * @see https://www.tutorialrepublic.com/php-reference/php-error-levels.php
     *
     * @param int $no
     *
     * @return string|null
     */
    private function errorLevel(int $no): ?string
    {
        switch ($no) {
            case 0:
                // For set_exception_handler.
                $level = 'E_EXCEPTION';
                break;
            case 1:
                // A fatal run-time error, that can't be recovered from. The execution of the script is stopped immediately.
                $level = 'E_ERROR';
                break;
            case 2:
                // A run-time warning. It is non-fatal and most errors tend to fall into this category. The execution of the script is not stopped.
                $level = 'E_WARNING';
                break;
            case 4:
                // The compile-time parse error. Parse errors should only be generated by the parser.
                $level = 'E_PARSE';
                break;
            case 8:
                // A run-time notice indicating that the script encountered something that could possibly an error, although the situation could also occur when running a script normally.
                $level = 'E_NOTICE';
                break;
            case 16:
                // A fatal error that occur during the PHP's engine initial startup. This is like an E_ERROR, except it is generated by the core of PHP.
                $level = 'E_CORE_ERROR';
                break;
            case 32:
                // A non-fatal error that occur during the PHP's engine initial startup. This is like an E_WARNING, except it is generated by the core of PHP.
                $level = 'E_CORE_WARNING';
                break;
            case 64:
                // A fatal error that occur while the script was being compiled. This is like an E_ERROR, except it is generated by the Zend Scripting Engine.
                $level = 'E_COMPILE_ERROR';
                break;
            case 128:
                // A non-fatal error occur while the script was being compiled. This is like an E_WARNING, except it is generated by the Zend Scripting Engine.
                $level = 'E_COMPILE_WARNING';
                break;
            case 256:
                // A fatal user-generated error message. This is like an E_ERROR, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine.
                $level = 'E_USER_ERROR';
                break;
            case 512:
                // A non-fatal user-generated warning message. This is like an E_WARNING, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine.
                $level = 'E_USER_ERROR';
                break;
            case 1024:
                // A user-generated notice message. This is like an E_NOTICE, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine.
                $level = 'E_USER_ERROR';
                break;
            case 2048:
                // Not strictly an error, but triggered whenever PHP encounters code that could lead to problems or forward incompatibilities.
                $level = 'E_STRICT';
                break;
            case 4096:
                // A catchable fatal error. Although the error was fatal, it did not leave the PHP engine in an unstable state. If the error is not caught by a user defined error handler (see set_error_handler()), the application aborts as it was an E_ERROR.
                $level = 'E_RECOVERABLE_ERROR';
                break;
            case 8192:
                // A run-time notice indicating that the code will not work in future versions of PHP.
                $level = 'E_DEPRECATED';
                break;
            case 16384:
                // A user-generated warning message. This is like an E_DEPRECATED, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine.
                $level = 'E_USER_DEPRECATED';
                break;
            case 32767:
                // All errors and warnings, except of level E_STRICT prior to PHP 5.4.0.
                $level = 'E_ALL';
                break;
            default:
                $level = null;
                break;
        }
        return $level;
    }

}