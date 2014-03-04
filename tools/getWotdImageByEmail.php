<?php

require_once __DIR__ . '/../phplib/util.php';
require_once __DIR__.'/../phplib/mime-mail-parser/MimeMailParser.class.php';

log_scriptLog("getWotdImageByEmail: starting");

$validSenderAddress = Config::get("WotD.imageEmailSender") or die("No image email sender in config file\r\n");
$validHeight = Config::get("WotD.wotdImageHeight") or die("No image height in config file\r\n");
$validWidth = Config::get("WotD.wotdImageWidth") or die("No image width in config file\r\n");
$daysInterval = Config::get("WotD.interval")or die("No days interval in config file\r\n");
$imgRoot = Config::get("WotD.imgRoot") or die("No img root in config file\r\n");

$email = getEmailFromStdin();
$Parser = new MimeMailParser();
$Parser->setText($email);

$sender = $Parser->getHeader("from");
$subject = $Parser->getHeader("subject");
$dateHeader = $Parser->getHeader("date");

if (!stristr($sender, $validSenderAddress))
{
    log_scriptLog("Ignoring message '$subject' due to invalid sender '$sender'");
    exit(0);
}

if (!stristr($subject, "WOTD"))
{
    log_scriptLog("Ignoring message '$subject' due to invalid subject");
    exit(0);
}
$wotd = GetWotdFromSubject($subject);


$attachments = $Parser->getAttachments();
if(empty($attachments))
{
    log_scriptLog("Ignoring message '$subject' because it has no attachments");
    exit(0);
}
elseif(count ($attachments) > 1)
{
    log_scriptLog("Ignoring message '$subject' because it has more than 1 attachment");
    exit(0);
}

$contentType = $attachments[0]->content_type;
if(!stristr($contentType, "image"))
{
    log_scriptLog("Ignoring message '$subject' because its attachment is not an image");
    exit(0);
    
}

$image = $attachments[0]->content;
$imageExtension = $attachments[0]->getFileExtension();
$tmpFilePath = sys_get_temp_dir().'/'.$wotd;
file_put_contents($tmpFilePath, $image);

list($height, $width) = getimagesize($tmpFilePath);

try
{
    if($height != $validHeight || $width != $validWidth)
    {
        throw new Exception("Înălţimea sau lăţimea imaginii nu este validă. Valorile valide sunt $height X $width");
    }
    
    $definitionEntryForWotd = Model::factory('Definition')
            ->where('lexicon', $wotd)
            ->find_one();
    
    if (!$definitionEntryForWotd)
    {
       throw new Exception("Cuvântul '$wotd' nu este un cuvânt valid (nu are o definiţie în baza de date).");
    }
    
    $definitionIdForWotd = $definitionEntryForWotd->id();
    
    $wotdRelEntry = Model::factory('WordOfTheDayRel')
            ->where('reftype', 'Definition')
            ->where('refid', $definitionIdForWotd)
            ->find_one();
    
    if (!$wotdRelEntry)
    {
       throw new Exception("Cuvântul '$wotd' nu este un cuvânt al zilei.");
    }
    
    $wotdEntry = Model::factory('WordOfTheDay')
            ->where('id', $wotdRelEntry->get('wotdId'))
            ->find_one();
    
    if (!$wotdEntry)
    {
       throw new Exception("Cuvântul '$wotd' nu este un cuvânt al zilei.");
    }
    
    $wotdImagePath = $wotdEntry->get('image');
    
    if($wotdImagePath != null)
    {
        throw new Exception("Cuvântul zilei '$wotd' are deja o imagine ataşată.");
    }
    
    $wotdDisplayDate = new DateTime($wotdEntry->get('displayDate'));
    $emailDate = new DateTime($dateHeader);    
    $daysDifference = $wotdDisplayDate->diff($emailDate, true)->days;
    
    if($daysDifference > $daysInterval)
    {
        throw new Exception("Aţi trimis cuvântul '$wotd' prea devreme/târziu. El va fi/a fost afişat  în data de ".$wotdDisplayDate->format("d-m-Y"));
    }
    
    $wotdImagePath = $imgRoot.'/wotd/'.$wotdDisplayDate->format('Y-m');
    if (!file_exists($wotdImagePath))
    {
        mkdir($wotdImagePath);
    }
    file_put_contents($wotdImagePath.'/'.$wotd.'.'.$imageExtension, $image);
    
    ReplyToEmail($sender, $subject, "Imaginea pentru cuvântul zilei '$wotd' a fost trimisă cu succes");
}
catch (Exception $e)
{
    log_scriptLog($e->getMessage());
    ReplyToEmail($sender, $subject, $e->getMessage());
}

function ReplyToEmail($senderAddress,$subject, $message)
{
    $sender = Config::get('WotD.sender', '');
    $replyto = Config::get('WotD.reply-to', '');
    $headers = array("From: $sender", "Reply-To: $replyto", 'Content-Type: text/plain; charset=UTF-8');
    $receiver = $senderAddress;
    
    mail($receiver, $subject, $message, implode("\r\n",$headers));
}

function GetWotdFromSubject($subject)
{
    $subject = strtolower($subject);
    $subject = str_replace("wotd", "", $subject);
    $subject = trim($subject, " \t:,");
    
    return $subject;
}

function getEmailFromStdin()
{
    $message = "";
    $stdinHandle = fopen('php://stdin', 'r');
    while($line = fgets($stdinHandle))
    {
        $message .= $line;
    }
    
    return $message;
}


