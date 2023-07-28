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
        foreach ($libraries as $libraryName) {
            if (empty(self::$librariesList[$libraryName])) {
                $librariesList[] = $libraryName;
            }
        }

        if (empty($librariesList)) {
            return;
        }
        $this->setLibrariesList($librariesList);
        $this->checkLibraryConfig($librariesList);

        foreach (self::$librariesList as $libraryName => $libraryConfig) {
            $this->webRestfulCheckList('library', null, $libraryConfig['path'], null, null);
        }
        $this->autoloaderLibrary();
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
        foreach ($libraries as $libraryName) {
            self::$librariesList[$libraryName] = $settingList[$libraryName];
        }
    }

    /**
     * Check libraries.ini config.
     *
     * @return void
     */
    public function checkLibraryConfig(array $librariesList)
    {
        $router = new Router();
        $libraryConfigList = ['path', 'oracle', 'mysql', 'mssql', 'postgres', 'mail', 'aws', 'libraries'];

        $model = [];
        $mail = [];
        $aws = [];
        foreach ($librariesList as $libraryName) {
            foreach ($libraryConfigList as $key) {
                if ($key == 'path') {
                    isset(self::$librariesList[$libraryName][$key]) ? null : die("【ERROR】Setting libraries.ini [$libraryName] tag \"$key\" is not exist.");
                } else {
                    if (isset(self::$librariesList[$libraryName][$key])) {
                        $lists = explode(',', self::$librariesList[$libraryName][$key]);
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
                            case 'libraries':
                                foreach ($lists as $key) {
                                    $libraries[] = trim($key);
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
            $prefix = 'Libraries';
            foreach (explode('/',substr( $libraryConfig['path'], 1)) as $value) {
                $prefix .= "\\". ucfirst($value);
            }
            $base_dir = $this->basePath . $libraryConfig['path'];
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