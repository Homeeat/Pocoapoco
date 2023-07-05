<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see            https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license    https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Services;

use Ntch\Pocoapoco\WebRestful\WebRestful;
use Ntch\Pocoapoco\WebRestful\Routing\Router;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;

class Base extends WebRestful
{

    /**
     * @var array
     */
    protected static array $servicesList = [];

    /**
     * Service entry point.
     *
     * @param array $services
     *
     * @return void
     */
    public function serviceBase(array $services)
    {
        if (!empty($services)) {
            $this->setServicesList($services);
            $this->checkServiceConfig();

            foreach (self::$servicesList as $serviceName => $serviceConfig) {
                $this->webRestfulCheckList('service', null, $serviceConfig['path'], null, null);
            }
            $this->autoloaderService();
        }
    }

    /**
     * Set service list.
     *
     * @param array $services
     *
     * @return void
     */
    private function setServicesList(array $services)
    {
        $settingBase = new SettingBase();
        $settingList = $settingBase->getSettingData('services');
        foreach ($services as $serviceName) {
            self::$servicesList[$serviceName] = $settingList[$serviceName];
        }
    }

    /**
     * Check services.ini config.
     *
     * @return void
     */
    public function checkServiceConfig()
    {
        $router = new Router();
        $serviceConfigList = ['path', 'oracle', 'mysql', 'mssql', 'postgres', 'mail', 'aws'];

        $model = [];
        $mail = [];
        $aws = [];
        foreach (self::$servicesList as $serviceName => $serviceConfig) {
            foreach ($serviceConfigList as $key) {
                if ($key == 'path') {
                    isset(self::$servicesList[$serviceName][$key]) ? null : die("【ERROR】Setting services.ini [$serviceName] tag \"$key\" is not exist.");
                } else {
                    if (isset(self::$servicesList[$serviceName][$key])) {
                        $lists = explode(',', self::$servicesList[$serviceName][$key]);
                        switch ($key) {
                            case 'oracle':
                                foreach ($lists as $key) {
                                    $model['oracle'][] = trim($key);
                                }
                                break;
                            case 'mysql':
                                foreach ($lists as $key) {
                                    $model['mysql'][] = trim($key);
                                }
                                break;
                            case 'mssql':
                                foreach ($lists as $key) {
                                    $model['mssql'][] = trim($key);
                                }
                                break;
                            case 'postgres':
                                foreach ($lists as $key) {
                                    $model['postgres'][] = trim($key);
                                }
                                break;
                            case 'mail':
                                foreach ($lists as $key) {
                                    $mail[] = trim($key);
                                }
                                break;
                            case 'aws':
                                foreach ($lists as $key) {
                                    $aws[] = trim($key);
                                }
                                break;
                        }

                    }

                }
            }
        }

        // model
        empty($model) ? null : $router->model($model, 'service');
        // mail
        empty($mail) ? null : $router->mail($mail, 'service');
        // aws
        empty($aws) ? null : $router->aws($aws, 'service');
    }

    /**
     * Autoloader service files from psr-4.
     *
     * @return void
     */
    private function autoloaderService()
    {
        foreach (self::$servicesList as $serviceName => $serviceConfig) {
            $prefix = str_replace('/', '\\', substr($serviceConfig['path'], 1));
            $base_dir = $this->basePath . $serviceConfig['path'];
            $this->autoloaderFile("services\\$prefix", $base_dir);
        }
    }

    /**
     * Get services list.
     *
     * @return array
     */
    public function getServicesList(): array
    {
        return self::$servicesList;
    }

}