<?php

/* Wrapper over PHPMailer with SMTP settings taken from Config.php */

require_once 'third-party/PHPMailer/Exception.php';
require_once 'third-party/PHPMailer/PHPMailer.php';
require_once 'third-party/PHPMailer/SMTP.php';

class Mailer {

  // dry run by default; real mode must be enabled explicitly
  private static $dryRun = true;

  // for dry runs - suppress verbose output
  private static $quiet = false;

  static function setRealMode() {
    self::$dryRun = false;
  }

  static function setQuietMode() {
    self::$quiet = true;
  }

  /**
   * $from: from address; should have corresponding credentials in the config file
   * $to: array of recipient addresses
   * $subject: subject line
   * $textBody: plain text body
   * $htmlBody: HTML body (optional)
   **/
  static function send($from, $to, $subject, $textBody, $htmlBody = null) {
    $info = self::getInfo($from);

    // set the from, to and subject fields
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setFrom($from, $info['name']);
    foreach ($to as $recipient) {
      $mail->addAddress($recipient);
    }
    $mail->Subject = $subject;

    // set the plaintext and/or html body
    if ($htmlBody) {
      $mail->Body    = $htmlBody;
      $mail->AltBody = $textBody;
    } else {
      $mail->Body    = $textBody;
    }

    // configure SMTP
    $mail->isSMTP();
    $mail->Host = Config::SMTP_SERVER;
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
    if (!self::$dryRun) {
      $mail->send();
    } else if (!self::$quiet) {
      $mail->Encoding = '8-bit';
      $mail->preSend();
      print $mail->getSentMIMEMessage();
      print $mail->Body;
    }
  }

  /**
   * Returns the name and SMTP password for this address. Throws an exception
   * if the values are undefined.
   **/
  static function getInfo($from) {
    $rec = Config::EMAIL_IDENTITIES[$from] ?? null;

    if (!$rec) {
      throw new Exception('No email identity found for ' . $from);
    }

    $rec['username'] = explode('@', $from)[0];
    return $rec;
  }
}
