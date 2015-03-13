<?php

class MailTask extends Phalcon\CLI\Task
{

    public function mainAction(array $params = array())
    {
        switch ($params[0]) {
            case 'test':
                static::cliSendMail(array(
                    'level' => 'NOTICE',
                    'catalog' => 'Test',
                    'msg' => '<h1>Hello World!</h1>'
                ));
                break;
            default:
                break;
        }
    }

    public function sendAction(array $params = array())
    {
        $filename = $params[0];
        error_log($filename);
        $params = json_decode(file_get_contents($filename), true);
        @unlink($filename);
        // $params = json_decode(base64_decode($params[0]), true);
        $params = array_merge(array(
            'title' => 'NO TITLE',
            'level' => 'notice',
            'catalog' => 'default',
            'msg' => 'NO BODY',
            'attaches' => array()
        ), $params);
        error_log('Start sending mail ...');
        $config = $this->config->app->mail;
        $toList = $config->to->toArray(); // 目的地址
        $title = $config->title;
        $from = $config->from; // 邮件显示的发送方
        $fromName = $config->fromName;
        $smtpHost = $config->host;
        $smtpPort = $config->port;
        $smtpUsername = $config->username;
        $smtpPassword = $config->password;
        error_log(sprintf('%s: %s', 'SMTP', $smtpHost));
        error_log(sprintf('%s: %s', 'PORT', $smtpPort));
        error_log(sprintf('%s: %s', 'USER', $smtpUsername));
        error_log(sprintf('%s: %s', 'PASS', '******'));
        error_log(sprintf('%s: %s<%s>', 'FROM', $fromName, $from));
        error_log(sprintf('%s: %s', 'TO', implode(';', array_map(function ($to)
        {
            return sprintf('%s<%s>', (isset($to[1]) ? $to[1] : $to[0]), $to[0]);
        }, $toList))));
        $subject = sprintf('[%s][%s][%s]%s', $title, $params['catalog'], $params['level'], $params['title']);
        error_log(sprintf('%s: %s', 'SUBJECT', $subject));
        $body = $params['msg'];
        $mail = new Mail\Adapter\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = 'tls';
        //
        //
        $mail->From = $from;
        $mail->FromName = $fromName;
        array_walk($toList, function ($to, $index, &$mail)
        {
            $mail->addAddress($to[0], (isset($to[1]) ? $to[1] : $to[0]));
        }, $mail);
        //
        $mail->WordWrap = 50;
        array_walk($params['attaches'], function ($at, $index, &$mail)
        {
            $mail->addAttachment($at);
        }, $mail);
        $mail->isHTML();
        //
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $mail->html2text($body, true);
        //
        if ($mail->send()) {
            error_log('Mail sent successfully.');
        } else {
            error_log('Mail sent failed.');
        }
    }

    public static function cliSendMail(array $params = array())
    {
        $filename = tempnam(sys_get_temp_dir(), 'mail-');
        $ret = file_put_contents($filename, json_encode($params));
        error_log($ret ? 'ok' : 'failed');
        $cmd = 'php ' . APP_PATH . '/app/cli.php Mail send ' . $filename;
        error_log($cmd);
        Misc::runInBackground($cmd);
    }
}