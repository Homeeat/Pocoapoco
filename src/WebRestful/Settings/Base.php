<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful\Settings;

use Ntch\Pocoapoco\WebRestful\WebRestful;

class Base extends WebRestful
{

    /**
     * @var array
     */
    protected static array $settingVariable = [];

    /**
     * Setting entry point.
     *
     * @param string $path
     * @param string $class
     *
     * @return void
     */
    public function settingBase(string $path, string $class)
    {
        $paths = ['/'];
        isset($_SERVER['ENVIRONMENT']) ? $paths[] = '/' . $_SERVER['ENVIRONMENT'] : null;
        foreach ($paths as $key => $value) {
            $fileExit = $this->webRestfulCheckList('setting', null, $value, $class, null);
            $fileExit ? $this->setSettingData($class, $this->absoluteFile) : null;
        }
    }

    /**
     * set ini file data.
     *
     * @param string $fileName
     * @param string $absoluteFile
     *
     * @return void
     */
    private function setSettingData(string $fileName, string $absoluteFile)
    {
        switch ($fileName) {
            case 'log':
                $process_sections = false;
                break;
            case 'libraries':
            case 'error':
            case 'mail':
            case 'aws':
            case 'project':
            case 'oracle':
            case 'mysql':
            case 'mssql':
            case 'postgre':
                $process_sections = true;
                break;
            default:
                die('【ERROR】Setting fileName is not exist.');
        }

        $data = parse_ini_file($absoluteFile, $process_sections);
        foreach ($data as $key => $value) {
            self::$settingVariable[$fileName][$key] = $value;
        }
    }

    /**
     * Get setting data.
     *
     * @param string $type
     *
     * @return array|null
     */
    public function getSettingData(string $type): ?array
    {
        return isset(self::$settingVariable[$type]) ? self::$settingVariable[$type] : null;
    }

}