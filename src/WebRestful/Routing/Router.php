<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Routing;

use Ntch\Pocoapoco\WebRestful\WebRestful;
use Ntch\Pocoapoco\WebRestful\Controllers\Base as ControllerBase;
use Ntch\Pocoapoco\WebRestful\Public\Base as PublicBase;
use Ntch\Pocoapoco\WebRestful\View\Base as ViewBase;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;
use Ntch\Pocoapoco\WebRestful\Models\Base as ModelBase;
use Ntch\Pocoapoco\WebRestful\Librarys\Base as LibraryBase;
use Ntch\Pocoapoco\Mail\Base as MailBase;
use Ntch\Pocoapoco\Aws\Base as AwsBase;
use PHPMailer\PHPMailer\Exception;

class Router
{

    /**
     * Router to mvc.
     *
     * @param string $uri
     * @param array $mvc
     *
     * @return void
     * @throws Exception
     */
    public function mvc(string $uri, array $mvc)
    {
        $webRestful = new WebRestful();
        $webRestful->withUriMatch($uri);
        $uriExist = $webRestful->checkUriMatch();

        if ($uriExist) {
            // model
            $modelList = ['oracle' => [], 'mysql' => [], 'mssql' => [], 'postgre' => []];
            foreach ($mvc as $key => $value) {
                if (isset($modelList[$key])) {
                    $modelList[$key] = $value;
                }
            }
            $this->model($modelList);

            // library
            isset($mvc['libraries']) ? $this->library($mvc['libraries']) : null;

            // mail
            isset($mvc['mail']) ? $this->mail($mvc['mail']) : null;

            // aws
            isset($mvc['aws']) ? $this->aws($mvc['aws']) : null;

            // controller
            $path = $mvc['controller'][0];
            $class = $mvc['controller'][1];
            $method = isset($mvc['controller'][2]) ? $mvc['controller'][2] : 'index';
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
        $controllerBase->controllerBase($uri, $path, $class, $method);
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
     * @param string $path
     * @param string $class
     *
     * @return void
     */
    public function setting(string $path, string $class)
    {
        $settingBase = new SettingBase();
        $settingBase->settingBase($path, $class);
    }

    /**
     * Include model.
     *
     * @param array $modelList
     *
     * @return void
     */
    private function model(array $modelList)
    {
        $modelBase = new ModelBase();
        foreach ($modelList as $driver => $models) {
            if (!empty($models)) {
                $this->setting('/', $driver);
                $modelBase->modelBase($driver, $models);
            }
        }
    }

    /**
     * Include library.
     *
     * @param array $libraries
     *
     * @return void
     */
    private function library(array $libraries)
    {
        $libraryBase = new LibraryBase();
        $this->setting('/', 'libraries');
        $libraryBase->libraryBase($libraries);
    }

    /**
     * Include mail.
     *
     * @param array $mail
     *
     * @return void
     * @throws Exception
     */
    public function mail(array $mail)
    {
        $mailBase = new MailBase();
        $this->setting('/', 'mail');
        $mailBase->mailBase($mail);
    }

    /**
     * Include aws.
     *
     * @param array $aws
     *
     * @return void
     */
    public function aws(array $aws)
    {
        $awsBase = new AwsBase();
        $this->setting('/', 'aws');
        $awsBase->awsBase($aws);
    }

}