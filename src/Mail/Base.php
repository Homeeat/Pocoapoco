<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author      Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see         https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license     https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Mail;

use Ntch\Pocoapoco\WebRestful\Settings\Base as SettingBase;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Base
{

    /**
     * @var array
     */
    protected static array $mailList = [];

    /**
     * @var SettingBase
     */
    private SettingBase $settingBase;

    /**
     * Mail entry point.
     *
     * @param array $mail
     * @param array $mvc
     *
     * @return void
     * @throws Exception
     */
    public function mailBase(array $mail, string $mvc)
    {
        $this->settingBase = new SettingBase();
        $this->setMailList($mvc);
        $this->checkMailConfig($mail, $mvc);
    }

    /**
     * Set mail list.
     *
     * @param string $mvc
     *
     * @return void
     */
    private function setMailList(string $mvc)
    {
        $settingList = $this->settingBase->getSettingData('mails');
        foreach ($settingList as $mailName => $mailInfo) {
            if (empty(self::$mailList[$mvc][$mailName])) {
                self::$mailList[$mvc][$mailName] = $mailInfo;
            }
        }
    }

    /**
     * Check model config.
     *
     * @param array $mail
     * @param string $mvc
     *
     * @return void
     */
    private function checkMailConfig(array $mail, string $mvc)
    {
        $mailConfigList = ['Host', 'Port', 'Username', 'Password', 'SMTPAuth', 'SMTPSecure', 'CharSet', 'SMTPDebug'];

        foreach ($mail as $mailName) {
            foreach ($mailConfigList as $key) {
                isset(self::$mailList[$mvc][$mailName][$key]) ? null : die("【ERROR】Setting mail.ini tag \"$key\" is not exist.");
            }
        }
    }

    /**
     * Connect mail server.
     *
     * @param string $mailName
     * @param string $mvc
     *
     * @return void
     * @throws Exception
     */
    public function connect(string $mailName, string $mvc)
    {
        $mailer = new PHPMailer();
        $mailer->isSMTP();
        $mailer->Host = self::$mailList[$mvc][$mailName]['Host'];
        $mailer->Port = self::$mailList[$mvc][$mailName]['Port'];
        $mailer->Username = self::$mailList[$mvc][$mailName]['Username'];
        $mailer->Password = self::$mailList[$mvc][$mailName]['Password'];
        $mailer->SMTPAuth = self::$mailList[$mvc][$mailName]['SMTPAuth'];
        $mailer->Timeout = self::$mailList[$mvc][$mailName]['Timeout'];

        $conn = $mailer->smtpConnect();

        if ($conn) {
            self::$mailList[$mvc][$mailName]['connect']['status'] = 'success';
            $mailer->smtpClose();
        } else {
            self::$mailList[$mvc][$mailName]['connect']['status'] = 'error';
        }

    }

    /**
     * Get mail list.
     *
     * @param string $mvc
     *
     * @return array
     */
    public function getMailList(string $mvc): array
    {
        $showData = [];
        if(isset(self::$mailList[$mvc])) {
            $showData = self::$mailList[$mvc];
            foreach (self::$mailList[$mvc] as $mailName => $mailInfo) {
                $showData[$mailName]['Password'] = '***************';
            }
        }

        return $showData;
    }

}