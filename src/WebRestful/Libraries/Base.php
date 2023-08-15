<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Libraries;

use Ntch\Pocoapoco\WebRestful\Routing\Router;
use Ntch\Pocoapoco\WebRestful\WebRestful;
use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;

class Base extends WebRestful
{

    /**
     * @var array
     */
    protected static array $librariesList = [];

    /**
     * Library entry point.
     *
     * @param array $libraries
     *
     * @return void
     */
    public function libraryBase(array $libraries)
    {
        if (empty($libraries)) {
            return;
        }

        $settingBase = new SettingBase();
        $settingList = $settingBase->getSettingData('libraries');
        foreach ($libraries as $libraryName) {
            if (!empty(self::$librariesList[$libraryName])) {
                continue;
            }
            // Set library list.
            self::$librariesList[$libraryName] = $settingList[$libraryName];
            $this->checkLibraryConfig($libraryName, self::$librariesList[$libraryName]);
            $this->webRestfulCheckList('library', null, self::$librariesList[$libraryName]['path'], null, null);
            $this->autoloaderLibrary();
        }
    }

    /**
     * Check libraries.ini config.
     *
     * @return void
     */
    public function checkLibraryConfig(string $libraryName, array $libraryConfig)
    {
        $router = new Router();
        $libraryConfigList = ['path', 'models', 'mails', 'aws', 'libraries'];

        $model = [];
        $mail = [];
        $aws = [];
        foreach ($libraryConfigList as $key) {
            if ($key == 'path') {
                isset($libraryConfig[$key]) ? null : die("【ERROR】Setting libraries.ini [$libraryName] tag \"$key\" is not exist.");
            } else {
                if (isset($libraryConfig[$key])) {
                    $lists = explode(',', $libraryConfig[$key]);
                    switch ($key) {
                        case 'models':
                            foreach ($lists as $key) {
                                $model[] = trim($key);
                            }
                            break;
                        case 'mails':
                            foreach ($lists as $key) {
                                $mail[] = trim($key);
                            }
                            break;
                        case 'aws':
                            foreach ($lists as $key) {
                                $aws[] = trim($key);
                            }
                            break;
                        case 'libraries':
                            foreach ($lists as $key) {
                                $libraries[] = trim($key);
                            }
                            break;
                    }

                }

            }
        }

        // model
        empty($model) ? null : $router->model($model, 'library');
        // mail
        empty($mail) ? null : $router->mail($mail, 'library');
        // aws
        empty($aws) ? null : $router->aws($aws, 'library');
        // libraries
        empty($libraries) ? null : $router->library($libraries);
    }

    /**
     * Autoloader library files from psr-4.
     *
     * @return void
     */
    private function autoloaderLibrary()
    {
        foreach (self::$librariesList as $libraryName => $libraryConfig) {
            $prefix = str_replace('/', '\\', substr($libraryConfig['path'], 1));
            $base_dir = $this->basePath . $libraryConfig['path'];
            $this->autoloaderFile("libraries\\$prefix", $base_dir);
        }
    }

    /**
     * Get libraries list.
     *
     * @return array
     */
    public function getLibrariesList(): array
    {
        return self::$librariesList;
    }


}