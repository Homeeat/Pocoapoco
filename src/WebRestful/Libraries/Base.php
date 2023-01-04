<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see            https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license    https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Libraries;

use Ntch\Pocoapoco\WebRestful\WebRestful;
use Ntch\Pocoapoco\WebRestful\Routing\Router;
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
        if (!empty($libraries)) {
            $this->setLibrariesList($libraries);
            $this->checkLibraryConfig();

            foreach (self::$librariesList as $libName => $libConfig) {
                $this->webRestfulCheckList('library', null, $libConfig['path'], null, null);
            }
            $this->autoloaderLibrary();
        }
    }

    /**
     * Set library list.
     *
     * @param array $libraries
     *
     * @return void
     */
    private function setLibrariesList(array $libraries)
    {
        $settingBase = new SettingBase();
        $settingList = $settingBase->getSettingData('libraries');
        foreach ($libraries as $libName) {
            self::$librariesList[$libName] = $settingList[$libName];
        }
    }

    /**
     * Check libraries.ini config.
     *
     * @return void
     */
    public function checkLibraryConfig()
    {
        $router = new Router();
        $libraryConfigList = ['path', 'oracle', 'mysql', 'mssql', 'postgre', 'mail', 'aws'];

        $model = [];
        $mail = [];
        $aws = [];
        foreach (self::$librariesList as $libName => $libConfig) {
            foreach ($libraryConfigList as $key) {
                if ($key == 'path') {
                    isset(self::$librariesList[$libName][$key]) ? null : die("【ERROR】Setting libraries.ini [$libName] tag \"$key\" is not exist.");
                } else {
                    if (isset(self::$librariesList[$libName][$key])) {
                        $lists = explode(',', self::$librariesList[$libName][$key]);
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
                            case 'postgre':
                                foreach ($lists as $key) {
                                    $model['postgre'][] = trim($key);
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
        empty($model) ? null : $router->model($model, 'library');
        // mail
        empty($mail) ? null : $router->mail($mail, 'library');
        // aws
        empty($aws) ? null : $router->aws($aws, 'library');
    }

    /**
     * Autoloader library files from psr-4.
     *
     * @return void
     */
    private function autoloaderLibrary()
    {
        foreach (self::$librariesList as $libName => $libConfig) {
            $prefix = str_replace('/', '\\', substr($libConfig['path'], 1));
            $base_dir = $this->basePath . $libConfig['path'];
            $this->autoloaderFile($prefix, $base_dir);
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