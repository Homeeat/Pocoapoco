<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Aws;

use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;

class Base
{

    /**
     * @var array|null
     */
    protected static ?array $awsList;

    /**
     * @var SettingBase
     */
    private SettingBase $settingBase;

    /**
     * Aws entry point.
     *
     * @param array $aws
     *
     * @return void
     */
    public function awsBase(array $aws)
    {
        $this->settingBase = new SettingBase();
        $this->setAwsList();
        $this->checkAwsConfig($aws);
    }

    /**
     * Set aws list.
     *
     * @return void
     */
    private function setAwsList()
    {
        $settingList = $this->settingBase->getSettingData('aws');
        self::$awsList = $settingList;
    }

    /**
     * Check model config.
     *
     * @param array $aws
     *
     * @return void
     */
    private function checkAwsConfig(array $aws)
    {
        $awsConfigList = ['version', 'region', 'key', 'secret'];

        foreach ($aws as $awsName) {
            foreach ($awsConfigList as $key) {
                isset(self::$awsList[$awsName][$key]) ? null : die("【ERROR】Setting aws.ini tag \"$key\" is not exist.");
            }
        }
    }

    /**
     * Get aws list.
     *
     * @return array
     */
    public function getAwsList(): array
    {
        $showData = [];
        if(isset(self::$awsList)) {
            $showData = self::$awsList;
            foreach (self::$awsList as $awsName => $awsInfo) {
                $showData[$awsName]['Password'] = '***************';
            }
        }

        return $showData;
    }

}