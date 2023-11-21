<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Controllers;

use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\Log\Base as LogBase;
use Ntch\Pocoapoco\WebRestful\Controllers\Base as ControllerBase;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingsBase;
use Ntch\Pocoapoco\WebRestful\Libraries\Base as LibrariesBase;
use Ntch\Pocoapoco\Mail\Base as MailBase;
use Ntch\Pocoapoco\Mail\Mail;
use Ntch\Pocoapoco\Aws\Base as AwsBase;
use Ntch\Pocoapoco\Aws\Aws;
use Ntch\Pocoapoco\Project\Base as ProjectBase;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;

class Controller
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
        $logBase = new LogBase();
        $librariesBase = new LibrariesBase();
        $mailBase = new MailBase();
        $awsBase = new AWSBase();
        $modelBase = new ModelBase();

        // controller
        $request = new \stdClass();

        $request->uuid = $controllerBase->getUuid();
        $request->url = $controllerBase->getUrl();
        $request->method = $controllerBase->getMethod();
        $request->uri = $controllerBase->getUri();
        $request->query = $controllerBase->getQuery();
        $request->input = $controllerBase->getInput();
        $request->attribute = $controllerBase->getAttribute();
        $request->authorization = $controllerBase->getAuthorization();
        $request->cookies = $controllerBase->getCookie();
        $request->files = $controllerBase->getFiles();
        $request->client['ip'] = $controllerBase->getUriHost();
        $request->client['port'] = $controllerBase->getUriPort();
        $request->time = $controllerBase->getTime();
        $request->headers = $controllerBase->getHeaders();

        $this->request = $request;

        // error
        $this->setting['error'] = $settingsBase->getSettingData('error');

        // log
        $this->setting['log'] = $logBase->getLogInfo();

        // libraries
        $librariesList = $librariesBase->getLibrariesList();
        if(!empty($librariesList)) {
            $this->setting['libraries'] = $librariesList;
        }

        // mail
        $settingMail = $mailBase->getMailList('controller');
        if (!empty($settingMail)) {
            $this->setting['mail'] = $settingMail;

            foreach ($this->setting['mail'] as $server => $config) {
                $this->mail[$server] = new Mail($server, 'controller');
            }
        }

        // aws
        $settingAws = $awsBase->getAwsList('controller');
        if (!empty($settingAws)) {
            $this->setting['aws'] = $settingAws;

            foreach ($this->setting['aws'] as $account => $config) {
                $this->aws[$account] = new Aws($account, 'controller');
            }
        }

        // project
        $projectBase = new ProjectBase();
        $settingProject = $projectBase->getProjectList();
        if (!is_null($settingProject)) {
            $this->setting['project'] = $settingProject;
        }

        // model
        $settingModels = $modelBase->getDatabaseList('controller');
        if(!empty($settingModels)) {
            foreach ($settingModels as $key => $value) {
                $this->setting[$key] = $value;
            }
        }

        $models = $modelBase->getDatabaseObject('controller');
        if (!empty($models)) {
            $this->model = $models;
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
        ErrorBase::triggerError($message, $errhttp, $sendmail);
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
        $context['ip'] = $this->request->client['ip'];
        $context['port'] = $this->request->client['port'];
        $context['url'] = $this->request->url;
        foreach ($tempContext as $key => $value) {
            $context[$key] = $value;
        }

        LogBase::log($level, $message, $context);
    }

}