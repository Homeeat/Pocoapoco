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

            foreach (self::$servicesList as $libName => $libConfig) {
                $this->webRestfulCheckList('service', null, $libConfig['path'], null, null);
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
        foreach ($services as $libName) {
            self::$servicesList[$libName] = $settingList[$libName];
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
        foreach (self::$servicesList as $libName => $libConfig) {
            foreach ($serviceConfigList as $key) {
                if ($key == 'path') {
                    isset(self::$servicesList[$libName][$key]) ? null : die("【ERROR】Setting services.ini [$libName] tag \"$key\" is not exist.");
                } else {
                    if (isset(self::$servicesList[$libName][$key])) {
                        $lists = explode(',', self::$servicesList[$libName][$key]);
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
        foreach (self::$servicesList as $libName => $libConfig) {
            $prefix = str_replace('/', '\\', substr($libConfig['path'], 1));
            $base_dir = $this->basePath . $libConfig['path'];
            $this->autoloaderFile($prefix, $base_dir);
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