<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author      Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see         https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license     https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Libraries;

use Ntch\Pocoapoco\Aws\Aws;
use Ntch\Pocoapoco\Aws\Base as AwsBase;
use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\Log\Base as LogBase;
use Ntch\Pocoapoco\Mail\Base as MailBase;
use Ntch\Pocoapoco\Mail\Mail;
use Ntch\Pocoapoco\WebRestful\Controllers\Base as ControllerBase;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingsBase;

class Library
{

    use \Ntch\Pocoapoco\Tools\Uuid;
    use \Ntch\Pocoapoco\Tools\Output;

    /**
     * Construct
     */
    public function __construct()
    {
        // config
        $controllerBase = new ControllerBase();
        $settingsBase = new SettingsBase();
        $mailBase = new MailBase();
        $awsBase = new AWSBase();
        $modelBase = new ModelBase();

        // controller
        $request = new \stdClass();

        $request->uuid = $controllerBase->getUuid();

        $this->request = $request;

        // error
        $this->setting['error'] = $settingsBase->getSettingData('error');

        // log
        $this->setting['log'] = $settingsBase->getSettingData('log');

        // mail
        $settingMail = $mailBase->getMailList('library');
        if (!empty($settingMail)) {
            $this->setting['mail'] = $settingMail;

            foreach ($this->setting['mail'] as $server => $config) {
                $this->mail[$server] = new Mail($server, 'library');
            }
        }

        // aws
        $settingAws = $awsBase->getAwsList('library');
        if (!empty($settingAws)) {
            $this->setting['aws'] = $settingAws;

            foreach ($this->setting['aws'] as $account => $config) {
                $this->aws[$account] = new Aws($account, 'library');
            }
        }

        // model
        $settingModels = $modelBase->getDatabaseList('library');
        foreach ($settingModels as $key => $value) {
            $this->setting[$key] = $value;
        }

        $models = $modelBase->getDatabaseObject('library');
        if (!empty($models)) {
            $this->models = $models;
        }

    }

    /**
     * Trigger error.
     *
     * @param string $message
     * @param int $errhttp
     * @param int $sendmail
     *
     * @return void
     */
    public function triggerError(string $message, int $errhttp, int $sendmail)
    {
        ErrorBase::triggerError($message, $errhttp, $sendmail, $this->request->uuid);
    }

    /**
     * Trigger error.
     * 【 type 】
     * - EMERGENCY：system is unusable.
     * - ALERT：action must be taken immediately.
     * - CRITICAL：critical conditions.
     * - ERROR：error conditions.
     * - WARNING：warning conditions.
     * - NOTICE：normal, but significant, condition.
     * - INFO：informational message.
     * - DEBUG：debug-level message.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log(string $level, string $message, array $context = [])
    {
        $tempContext = $context;
        unset($context);
        $context['uuid'] = $this->request->uuid;
        $context['date'] = date('Y-m-d');
        $context['time'] = date('H:i:s');
        foreach ($tempContext as $key => $value) {
            $context[$key] = $value;
        }

        LogBase::log($level, $message, $context);
    }

}