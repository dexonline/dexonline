<?php

/* Wrapper over PHPMailer with SMTP settings taken from Config.php */

require_once 'third-party/PHPMailer/Exception.php';
require_once 'third-party/PHPMailer/PHPMailer.php';
require_once 'third-party/PHPMailer/SMTP.php';

class Mailer {
  /**
   * $from: from address; should have corresponding credentials in the config file
   * $to: array of recipient addresses
   * $subject: subject line
   * $textBody: plain text body
   * $htmlBody: HTML body (optional)
   * $headers: array of headers (optional)
   **/
  static function send($from, $to, $subject, $textBody, $htmlBody = null, $headers = []) {
    $info = self::getInfo($from);

    // set the from, to and subject fields
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setFrom($from, $info['name']); // TODO: lookup in cfg
    foreach ($to as $recipient) {
      $mail->addAddress($recipient);
    }
    $mail->Subject = $subject;

    // set the plaintext and/or html body
    if ($htmlBody) {
      $mail->Body    = $htmlBody;
      $mail->AltBody = $textBody;
      //      $mail->addCustomHeader('Content-Type', 'text/plain; charset=UTF-8');
    } else {
      $mail->Body    = $textBody;
    }

    // set additional headers
    foreach ($headers as $key => $value) {
      $mail->addCustomHeader($key, $value);
    }

    // configure SMTP
    $mail->isSMTP();
    $mail->Host = Config::get('mail.smtpServer');
    $mail->Username = $info['username'];
    $mail->Password = $info['password'];
    $mail->SMTPAuth = true;

    $mail->SMTPOptions = [
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
      ],
    ];

    // ship it!
    $mail->send();
  }

  /**
   * Returns the name and SMTP password for this address. Throws an exception
   * if the values are undefined.
   **/
  static function getInfo($from) {
    $names = Config::get('mail.name', []);
    $name = $names[$from] ?? null;

    if (!$name) {
      throw new Exception('No from name found for ' . $from);
    }

    $passwords = Config::get('mail.password', []);
    $password = $passwords[$from] ?? null;

    if (!$password) {
      throw new Exception('No credentials found for ' . $from);
    }

    $username = explode('@', $from)[0];

    return [
      'username' => $username,
      'name' => $name,
      'password' => $password,
    ];
  }
}
