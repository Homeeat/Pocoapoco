<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see            https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license    https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Routing;

use Ntch\Pocoapoco\WebRestful\WebRestful;
use Ntch\Pocoapoco\WebRestful\Controllers\Base as ControllerBase;
use Ntch\Pocoapoco\WebRestful\Public\Base as PublicBase;
use Ntch\Pocoapoco\WebRestful\View\Base as ViewBase;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;
use Ntch\Pocoapoco\WebRestful\Libraries\Base as LibraryBase;
use Ntch\Pocoapoco\Mail\Base as MailBase;
use Ntch\Pocoapoco\Aws\Base as AwsBase;
use Ntch\Pocoapoco\Log\Base as LogBase;
use PHPMailer\PHPMailer\Exception;

class Router
{

    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * @var array
     */
    protected static array $mixModelList = [];

    /**
     * Router to mix.
     *
     * @param string $uri
     * @param array $mix
     *
     * @return void
     * @throws Exception
     */
    public function mix(string $uri, array $mix)
    {
        $webRestful = new WebRestful();
        $webRestful->withUriMatch($uri);
        $uriExist = $webRestful->checkUriMatch();

        if ($uriExist) {
            // library
            isset($mix['libraries']) ? $this->library($mix['libraries']) : null;

            // model
            isset($mix['models']) ? $this->model($mix['models'], 'controller') : null;

            // mail
            isset($mix['mails']) ? $this->mail($mix['mails'], 'controller') : null;

            // aws
            isset($mix['aws']) ? $this->aws($mix['aws'], 'controller') : null;

            // logs
            isset($mix['log']) ? $this->log($mix['log']) : null;

            // controller
            $path = $mix['controller'][0];
            $class = $mix['controller'][1];
            $method = isset($mix['controller'][2]) ? $mix['controller'][2] : 'index';
            $this->controller($uri, $path, $class, $method);
        }
    }

    /**
     * Router to controller.
     *
     * @param string $uri
     * @param string $path
     * @param string $class
     * @param string $method
     *
     * @return void
     */
    public function controller(string $uri, string $path, string $class, string $method = 'index')
    {
        $controllerBase = new ControllerBase();
        $controllerBase->controllerBase($uri, $path, $class, $method, self::uuid());
    }

    /**
     * Router to public.
     *
     * @param string $path
     * @param string $class
     *
     * @return void
     */
    public function public(string $path, string $class)
    {
        $publicBase = new PublicBase();
        $publicBase->publicBase($path, $class);
    }

    /**
     * Router to view.
     *
     * @param string|null $uri
     * @param string $path
     * @param string $class
     * @param array $data
     *
     * @return void
     */
    public function view(string $uri = null, string $path, string $class, array $data = [])
    {
        $viewBase = new ViewBase();
        $viewBase->viewBase($uri, $path, $class, $data);
    }

    /**
     * Router to setting.
     *
     * @param string $class
     *
     * @return void
     */
    public function setting(string $class)
    {
        $settingBase = new SettingBase();
        $settingBase->settingBase($class);
    }

    /**
     * Include model.
     *
     * @param array $modelList
     * @param string $mvc
     *
     * @return void
     */
    public function model(array $models, string $mvc)
    {
        $modelBase = new ModelBase();
        $this->setting('models');
        $modelBase->modelBase($models, $mvc);
    }

    /**
     * Include library.
     *
     * @param array $libraries
     *
     * @return void
     */
    public function library(array $libraries)
    {
        $libraryBase = new LibraryBase();
        $this->setting('libraries');
        $libraryBase->libraryBase($libraries);
    }

    /**
     * Include mail.
     *
     * @param array $mail
     * @param string $mvc
     *
     * @return void
     * @throws Exception
     */
    public function mail(array $mail, string $mvc)
    {
        $mailBase = new MailBase();
        $this->setting('mails');
        $mailBase->mailBase($mail, $mvc);
    }

    /**
     * Include aws.
     *
     * @param array $aws
     * @param string $mvc
     *
     * @return void
     */
    public function aws(array $aws, string $mvc)
    {
        $awsBase = new AwsBase();
        $this->setting('aws');
        $awsBase->awsBase($aws, $mvc);
    }

    /**
     * Include log.
     *
     * @param string $log
     * @return void
     */
    public function log(string $log)
    {
        $logBase = new LogBase();
        $this->setting('logs');
        $logBase->logBase($log);
    }

}