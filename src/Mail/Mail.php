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

use Ntch\Pocoapoco\Mail\Base as MailBase;
use Ntch\Pocoapoco\Error\Base as ErrorBase;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail extends MailBase
{

    /**
     * @var string
     */
    protected string $mailName;

    /**
     * @var array
     */
    protected array $mailer = [];

    /**
     * Construct
     *
     * @param string $mailName
     *
     * @return void
     */
    public function __construct(string $mailName)
    {
        // mail config
        $this->mailName = $mailName;
        $this->mailer[$mailName] = new PHPMailer();
        $this->mailer[$mailName]->isSMTP();
        $this->mailer[$mailName]->Host = self::$mailList[$mailName]['Host'];
        $this->mailer[$mailName]->Port = self::$mailList[$mailName]['Port'];
        $this->mailer[$mailName]->Username = self::$mailList[$mailName]['Username'];
        $this->mailer[$mailName]->Password = self::$mailList[$mailName]['Password'];
        $this->mailer[$mailName]->SMTPAuth = self::$mailList[$mailName]['SMTPAuth'];
        $this->mailer[$mailName]->SMTPSecure = self::$mailList[$mailName]['SMTPSecure'];
        $this->mailer[$mailName]->CharSet = self::$mailList[$mailName]['CharSet'];
        $this->mailer[$mailName]->SMTPDebug = self::$mailList[$mailName]['SMTPDebug'];
        $this->mailer[$mailName]->Timeout = self::$mailList[$mailName]['Timeout'];
    }

    /**
     * Send mail.
     *
     * @return boolean
     */
    public function send(): bool
    {
        $res = $this->mailer[$this->mailName]->send();
        $this->clear();

        return $res;
    }

    /**
     * clear mail data config.
     *
     * @return void
     */
    public function clear()
    {
        $this->mailer[$this->mailName]->clearAddresses();
        $this->mailer[$this->mailName]->clearCCs();
        $this->mailer[$this->mailName]->clearBCCs();
        $this->mailer[$this->mailName]->clearReplyTos();
        $this->mailer[$this->mailName]->clearAllRecipients();
        $this->mailer[$this->mailName]->clearAttachments();
        $this->mailer[$this->mailName]->clearCustomHeaders();
        $this->mailer[$this->mailName]->clearCustomHeaders();
    }

    /**
     * Set mail header.
     *
     * @param array $header
     *
     * @return object
     */
    public function header(array $header): object
    {
        foreach ($header as $key => $value) {
            $this->mailer[$this->mailName]->addCustomHeader($key, $value);
        }

        return $this;
    }

    /**
     * Set mail subject.
     *
     * @param string $subject
     *
     * @return object
     */
    public function subject(string $subject): object
    {
        $this->mailer[$this->mailName]->Subject = $subject;

        return $this;
    }

    /**
     * Set mail from.
     *
     * @param array $from
     *
     * @return object
     */
    public function from(array $from): object
    {
        $adress = array_keys($from)[0];
        $name = array_values($from)[0];
        filter_var($adress, FILTER_VALIDATE_EMAIL) ? $this->mailer[$this->mailName]->From = $adress : ErrorBase::triggerError("Mail from adress is illegal. Place check from adress： $from", 4, 0);
        $this->mailer[$this->mailName]->FromName = $name;

        return $this;
    }

    /**
     * Set mail to.
     *
     * @param array $to
     *
     * @return object
     */
    public function to(array $to): object
    {
        foreach ($to as $adress => $name) {
            filter_var($adress, FILTER_VALIDATE_EMAIL) ? null : ErrorBase::triggerError("Mail to adress is illegal. Place check from adress： $adress", 4, 0);
            $this->mailer[$this->mailName]->addAddress($adress, $name);
        }

        return $this;
    }

    /**
     * Set mail cc.
     *
     * @param array $cc
     *
     * @return object
     */
    public function cc(array $cc): object
    {
        foreach ($cc as $adress => $name) {
            filter_var($adress, FILTER_VALIDATE_EMAIL) ? null : ErrorBase::triggerError("Mail cc adress is illegal. Place check from adress： $adress", 4, 0);
            $this->mailer[$this->mailName]->addCC($adress, $name);
        }

        return $this;
    }

    /**
     * Set mail bcc.
     *
     * @param array $bcc
     *
     * @return object
     */
    public function bcc(array $bcc): object
    {
        foreach ($bcc as $adress => $name) {
            filter_var($adress, FILTER_VALIDATE_EMAIL) ? null : ErrorBase::triggerError("Mail bcc adress is illegal. Place check from adress： $adress", 4, 0);
            $this->mailer[$this->mailName]->addBCC($adress, $name);
        }

        return $this;
    }

    /**
     * Set mail content.
     * - source => user , content => string
     * - source => url , content => string (url)
     * - source => local , content => string (path)
     * - type => text | html
     *
     * @param string $source
     * @param string $type
     * @param string $content
     * @param array $data
     *
     * @return object
     */
    public function content(string $source, string $type, string $content, array $data = []): object
    {
        if (strtolower($type) === 'html') {
            $this->mailer[$this->mailName]->isHTML(true);
        } else {
            $this->mailer[$this->mailName]->isHTML(false);
        }

        switch ($source) {
            case 'user':
                $this->mailer[$this->mailName]->Body = $content;
                break;
            case 'url':
                $this->mailer[$this->mailName]->Body = file_get_contents($content);
                break;
            case 'local':
                if (is_file($content)) {
                    ob_start();
                    $this->data = $data;
                    include($content);
                    $this->mailer[$this->mailName]->Body = ob_get_contents();
                    ob_end_clean();
                } else {
                    ErrorBase::triggerError("File does not exist. Please create the file in the following path： $content", 4, 0);
                }
                break;
        }

        return $this;
    }

    /**
     * Set mail attachment.
     * - source => local , content => string (path)
     * - source => url , content => string (url)
     *
     * @param string $source
     * @param string $attachment
     *
     * @return object
     */
    public function attachment(string $source, string $attachment): object
    {
        switch ($source) {
            case 'local':
                is_file($attachment) ? $this->mailer[$this->mailName]->addAttachment($attachment) : ErrorBase::triggerError("Attachment does not exist. Please create the file in the following path： $attachment", 4, 0);
                break;
            case 'url':
                $fileName = explode('/', $attachment);
                filter_var($attachment, FILTER_VALIDATE_URL) ? $this->mailer[$this->mailName]->addStringAttachment(file_get_contents($attachment), end($fileName)) : ErrorBase::triggerError("Attachment does not exist. Please check the url ： $attachment", 4, 0);
                break;
        }

        return $this;
    }

    /**
     * Set mail body image.
     * - source => local , content => string (path)
     * - source => url , content => string (url)
     *
     * @param string $path
     * @param string $cid
     * @param string $name
     *
     * @return object
     */
    public function image(string $path, string $cid, string $name): object
    {
        $this->mailer[$this->mailName]->addEmbeddedImage($path, $cid, $name);

        return $this;
    }

}