<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author      Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see         https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license     https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
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
     * @param string $mvc
     *
     * @return void
     */
    public function awsBase(array $aws, string $mvc)
    {
        $this->settingBase = new SettingBase();
        $this->setAwsList($mvc);
        $this->checkAwsConfig($aws, $mvc);
    }

    /**
     * Set aws list.
     *
     * @param string $mvc
     *
     * @return void
     */
    private function setAwsList(string $mvc)
    {
        $settingList = $this->settingBase->getSettingData('aws');
        self::$awsList[$mvc] = $settingList;
    }

    /**
     * Check model config.
     *
     * @param array $aws
     * @param string $mvc
     *
     * @return void
     */
    private function checkAwsConfig(array $aws, string $mvc)
    {
        $awsConfigList = ['version', 'region', 'key', 'secret'];

        foreach ($aws as $awsName) {
            foreach ($awsConfigList as $key) {
                isset(self::$awsList[$mvc][$awsName][$key]) ? null : die("【ERROR】Setting aws.ini tag \"$key\" is not exist.");
            }
        }
    }

    /**
     * Get aws list.
     *
     * @param string $mvc
     *
     * @return array
     */
    public function getAwsList(string $mvc): array
    {
        $showData = [];
        if(isset(self::$awsList[$mvc])) {
            $showData = self::$awsList[$mvc];
            foreach (self::$awsList[$mvc] as $awsName => $awsInfo) {
                $showData[$awsName]['Password'] = '***************';
            }
        }

        return $showData;
    }

}