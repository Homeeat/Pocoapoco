<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Mail;

use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Base
{

    /**
     * @var array|null
     */
    protected static ?array $mailList;

    /**
     * @var SettingBase
     */
    private SettingBase $settingBase;

    /**
     * Mail entry point.
     *
     * @param array $mail
     *
     * @return void
     * @throws Exception
     */
    public function mailBase(array $mail)
    {
        $this->settingBase = new SettingBase();
        $this->setMailList();
        $this->checkMailConfig($mail);
        foreach ($mail as $keyName) {
            $this->connect($keyName);
        }
    }

    /**
     * Set mail list.
     *
     * @return void
     */
    private function setMailList()
    {
        $settingList = $this->settingBase->getSettingData('mail');
        self::$mailList = $settingList;
    }

    /**
     * Check model config.
     *
     * @param array $mail
     *
     * @return void
     */
    private function checkMailConfig(array $mail)
    {
        $mailConfigList = ['Host', 'Port', 'Username', 'Password', 'SMTPAuth', 'SMTPSecure', 'CharSet', 'SMTPDebug'];

        foreach ($mail as $mailName) {
            foreach ($mailConfigList as $key) {
                isset(self::$mailList[$mailName][$key]) ? null : die("【ERROR】Setting mail.ini tag \"$key\" is not exist.");
            }
        }
    }

    /**
     * Connect mail server.
     *
     * @param string $mailName
     *
     * @return void
     * @throws Exception
     */
    public function connect(string $mailName)
    {
        $mailer = new PHPMailer();
        $mailer->isSMTP();
        $mailer->Host = self::$mailList[$mailName]['Host'];
        $mailer->Port = self::$mailList[$mailName]['Port'];
        $mailer->Username = self::$mailList[$mailName]['Username'];
        $mailer->Password = self::$mailList[$mailName]['Password'];
        $mailer->SMTPAuth = self::$mailList[$mailName]['SMTPAuth'];
        $mailer->Timeout = self::$mailList[$mailName]['Timeout'];

        $conn = $mailer->smtpConnect();

        if ($conn) {
            self::$mailList[$mailName]['connect']['status'] = 'success';
            $mailer->smtpClose();
        } else {
            self::$mailList[$mailName]['connect']['status'] = 'error';
        }

    }

    /**
     * Get mail list.
     *
     * @return array
     */
    public function getMailList(): array
    {
        $showData = [];
        if(isset(self::$mailList)) {
            $showData = self::$mailList;
            foreach (self::$mailList as $mailName => $mailInfo) {
                $showData[$mailName]['Password'] = '***************';
            }
        }

        return $showData;
    }


}