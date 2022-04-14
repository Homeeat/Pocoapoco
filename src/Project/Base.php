<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Project;

use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingsBase;

class Base
{

    /**
     * @var array|null
     */
    protected static ?array $project;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setProjectInfo();
    }

    /**
     * Set data from project.ini.
     *
     * @return void
     */
    private function setProjectInfo()
    {
        $setting = new SettingsBase();
        $setting->settingBase('/', 'project');
        self::$project = $setting->getSettingData('project');
    }

    /**
     * Get project list.
     *
     * @return array|null
     */
    public function getProjectList(): ?array
    {
        return self::$project;
    }

}