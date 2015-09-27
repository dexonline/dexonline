<?php

require_once __DIR__ . '/../phplib/util.php';
require_once __DIR__ . '/../phplib/mime-mail-parser/MimeMailParser.class.php';

log_scriptLog("getWotdImageByEmail: starting");

$dryRun = false;
foreach ($argv as $i => $arg) {
  if ($i) {
    switch ($arg) {
    case '--dry-run': $dryRun = true; break;
    default: print "Unknown flag $arg -- aborting\n"; exit;
    }
  }
}

$validHeight = Config::get("WotD.wotdImageHeight") or die("No image height in config file\r\n");
$validWidth = Config::get("WotD.wotdImageWidth") or die("No image width in config file\r\n");
$daysInterval = Config::get("WotD.interval")or die("No days interval in config file\r\n");

$email = file_get_contents("php://stdin");
$Parser = new MimeMailParser();
$Parser->setText($email);

$sender = $Parser->getHeader("from");
$subject = imap_utf8($Parser->getHeader("subject"));

$parsedSender = mailparse_rfc822_parse_addresses($sender);
if (count($parsedSender) != 1) {
  OS::errorAndExit("Ignoring message '$subject' due to invalid sender '$sender'", 0);
}

$from = $parsedSender[0]['address'];
$artist = WotdArtist::get_by_email($from);
if (!$artist) {
  OS::errorAndExit("Ignoring message '$subject' because sender '$from' is not a WotD artist", 0);
}

$word = GetWotdFromSubject($subject);

$attachments = $Parser->getAttachments();
if (empty($attachments)) {
  OS::errorAndExit("Ignoring message '$subject' because it has no attachments", 0);
} elseif (count($attachments) > 1) {
  OS::errorAndExit("Ignoring message '$subject' because it has more than 1 attachment", 0);
}

$contentType = $attachments[0]->content_type;
if (!StringUtil::startsWith($contentType, "image/")) {
  OS::errorAndExit("Ignoring message '$subject' because its attachment is not an image", 0);
}

$image = $attachments[0]->content;
$imageExtension = $attachments[0]->getFileExtension();
$tmpFilePath = tempnam(null, 'wotd_');
file_put_contents($tmpFilePath, $image);

list($height, $width) = getimagesize($tmpFilePath);

try {
  if ($height != $validHeight || $width != $validWidth) {
    throw new Exception("Imaginea trebuie să aibă dimensiuni {$validWidth} x {$validHeight}.");
  }

  $dateMin = date('Y-m-d', strtotime("-{$daysInterval} day"));
  $dateMax = date('Y-m-d', strtotime("+{$daysInterval} day"));
  $wotds = Model::factory('WordOfTheDay')
    ->table_alias('wotd')
    ->select('wotd.*')
    ->distinct()
    ->join('WordOfTheDayRel', 'wotd.id = rel.wotdId', 'rel')
    ->join('LexemDefinitionMap', 'rel.refId = ldm.definitionId', 'ldm')
    ->join('Lexem', 'ldm.lexemId = l.id', 'l')
    ->join('LexemModel', 'lm.lexemId = l.id', 'lm')
    ->join('InflectedForm', 'i.lexemModelId = lm.id', 'i')
    ->where('i.formUtf8General', $word)
    ->where_gte('wotd.displayDate', $dateMin)
    ->where_lte('wotd.displayDate', $dateMax)
    ->find_many();

  if (!count($wotds)) {
    throw new Exception(sprintf( "Cuvântul '%s' nu apare în intervalul %s - %s.", $word, $dateMin, $dateMax));
  } else if (count($wotds) > 1) {
    throw new Exception(sprintf( "Cuvântul '%s' apare de %d ori în intervalul %s - %s.", $word, count($wotds), $dateMin, $dateMax));
  }
  $wotd = $wotds[0];

  // No exception if no artist is assigned -- that's probably simply an ommission.
  $artist = WotdArtist::getByDate($wotd->displayDate);
  if ($artist && $artist->email != $from) {
    throw new Exception("Cuvântul '$word' îi este alocat altui artist.");
  }

  $today = date('Y-m-d');
  if ($wotd->image && ($wotd->displayDate < $today)) {
    throw new Exception("Cuvântul zilei '$word' are deja o imagine ataşată. Nu puteți modifica imaginile cuvintelor din trecut.");
  }

  $wotdDisplayDate = new DateTime($wotd->displayDate);
  $wotd->image = sprintf("%s/%s.%s", $wotdDisplayDate->format('Y-m'), $word, $imageExtension);
  $wotd->save();
  $wotdImagePath = '/img/wotd/' . $wotd->image;
  $f = new FtpUtil();
  $f->staticServerPut($tmpFilePath, $wotdImagePath);
  unlink($tmpFilePath);
    
  ReplyToEmail($sender, $subject, "Am adăugat imaginea pentru '{$word}'.");

} catch (Exception $e) {
  unlink($tmpFilePath);
  log_scriptLog($e->getMessage());
  ReplyToEmail($sender, $subject, $e->getMessage());
}

log_scriptLog("getWotdImageByEmail: done");

/***************************************************************************/

function ReplyToEmail($senderAddress, $subject, $message) {
  global $dryRun;

  $sender = Config::get('WotD.sender');
  $replyto = Config::get('WotD.reply-to');
  $headers = array("From: $sender", "Reply-To: $replyto", 'Content-Type: text/plain; charset=UTF-8');

  if ($dryRun) {
    print "---- DRY RUN ----\n";
    print "Către: $senderAddress\nSubiect: Re: $subject\n\n$message\n";
  } else {
    mail($senderAddress, "Re: $subject", $message, implode("\r\n", $headers));
  }
}

function GetWotdFromSubject($subject) {
  $parts = preg_split("/\\s+/", trim($subject));
  if (count($parts) != 2) {
     OS::errorAndExit("Ignoring message '$subject' due to invalid subject", 0);
  }
  if ($parts[0] != Config::get('WotD.password')) {
    OS::errorAndExit("Ignoring message '$subject' due to invalid password in the subject", 0);
  }
  return $parts[1];
}
