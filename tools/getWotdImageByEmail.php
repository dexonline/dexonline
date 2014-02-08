<?php

require_once __DIR__ . '/../phplib/util.php';
log_scriptLog("getWotdImageByEmail: starting");

define('FOLDER', 'INBOX');

$username = Config::get('WotD.imageEmailUsername');
$password = Config::get('WotD.imageEmailPassword');
$ssl = (bool)Config::get('WotD.imageEmailSSL');
$host = Config::get('WotD.imageEmailHost');
$port = Config::get('WotD.imageEmailPort');
$wotdImgFolder = Config::get('WotD.newWotdImageFolder','.');

//open connection to IMAP mail server
$sslString = $ssl? '':'/novalidate-cert';
$mailBox = '{'."$host:$port/ssl$sslString".'}'."INBOX";
$connection = imap_open($mailBox, $username, $password);

$criteria = 'UNSEEN SUBJECT "WOTD"';
$unreadWotDMessageNumbers = imap_search($connection, $criteria);

if (!$unreadWotDMessageNumbers)
{
    log_scriptLog("no new wotd emails found");
    exit;
}

foreach($unreadWotDMessageNumbers as $messageNumber)
{
    $header = imap_headerinfo($connection, $messageNumber);
    log_scriptLog("Found unread message. Subject: $header->subject");
    
    $wotd = GetWotdFromSubject($header->subject);
    $image = GetImageFromAttachment($connection, $messageNumber);
    
    try
    {
        if (!$image)
        {
            throw new Exception("Could not extract image from email attachment.");
        }
       
        $definitionForWotd = Model::factory('Definition')->where('lexicon', $wotd)->find_one();

        if (!$definitionForWotd)
        {
            throw new Exception("No word matching $wotd found in definition database.");
        }
        
        if (!file_exists($wotdImgFolder))
        {
            mkdir ($wotdImgFolder);
        }
        file_put_contents("$wotdImgFolder/$wotd.jpg", $image);
        
        //send reply
        ReplyToEmail($header, "Word of the day image submitted successfully. This is an automated reply.");
        
    }
    catch (Exception $e)
    {
        log_scriptLog($e->getMessage());
        ReplyToEmail($header, $e->getMessage());
    }
    
    //mark message as read
    imap_setflag_full($connection, $messageNumber, "\Seen");
    
    log_scriptLog("finished collecting wotd images from email");
}

function ReplyToEmail($header, $message)
{
    $sender = Config::get('WotD.sender', '');
    $replyto = Config::get('WotD.reply-to', '');
    $headers = array("From: $sender", "Reply-To: $replyto", 'Content-Type: text/plain; charset=UTF-8');
    $receiver = $header->senderaddress;
    
    mail($receiver, $header->subject, $message, implode("\r\n",$headers));
}

function GetWotdFromSubject($subject)
{
    $subject = strtolower($subject);
    $subject = str_replace("wotd", "", $subject);
    $subject = trim($subject, " \t:,");
    
    return $subject;
}

function GetImageFromAttachment($connection, $messageNumber)
{
    $bodyparts = imap_fetchstructure($connection,$messageNumber)->parts;
    if (is_null($bodyparts) || empty($bodyparts)) return null;
    
    foreach($bodyparts as $partNo => $part)
    {
        if (strcmp(strtolower($part->disposition), "attachment" ) == 0)
        {
            return Decode($connection, $messageNumber, $partNo+1);
        }
        
    }
    return null;
}

function Decode($connection, $messageNumber, $partNo)
{
    $data = \imap_fetchbody($connection, $messageNumber, $partNo);
    return base64_decode($data);
}


