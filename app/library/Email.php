<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/23 17:04
 */

namespace app\library;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    protected array $config;

    protected string $subject;
    protected string $body;
    protected bool   $isHtml = false;

    protected PHPMailer $client;

    /**
     * @param array $config
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();                                      // 设置邮件使用SMTP
        $mail->SMTPAuth = true;                               // 开启SMTP认证
        $mail->Host = $config['smtp_server'];                 // 指定主SMTP服务器
        $mail->Username = $config['smtp_account'];            // SMTP用户名
        $mail->Password = $config['smtp_password'];           // SMTP密码
        $mail->SMTPSecure = $config['smtp_encryption'];       // 启用加密模式
        $mail->Port = $config['smtp_port'];                   // TCP端口号

        //设置发件人
        $mail->setFrom($this->config['smtp_sender_email'], $this->config['smtp_sender_name'] ?: '');

        $this->client = $mail;
    }

    public function withSubject($subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function withBody($body, $isHtml = false): static
    {
        $this->body = $body;
        $this->isHtml = $isHtml;
        return $this;
    }

    /**
     * @param string $address
     * @param array|null $params
     * @return bool
     * @throws Exception
     */
    public function to(string $address, array $params = null): bool
    {
        $this->client->addAddress($address);
        $this->client->isHTML($this->isHtml);

        if (!$params) $params = [];
        $params['{subject}'] = $this->subject;
        $this->client->Body = str_replace(array_keys($params), array_values($params), $this->body);
        $this->client->Subject = $this->subject;

        return $this->client->send();
    }
}