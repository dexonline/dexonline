<?php
/**
 * This script checks whether exactly one WotD is set and whether it has an image, for the upcoming N days.
 **/

require_once __DIR__ . '/../phplib/util.php';

Log::notice('started');

define('NUM_DAYS', 3);

$rcptInfo = Config::get('WotD.rcpt-info',array());
$rcptError = Config::get('WotD.rcpt-error',array());
$sender = Config::get('WotD.sender', '');
$replyto = Config::get('WotD.reply-to', '');
$MAIL_HEADERS = array("From: $sender", "Reply-To: $replyto", 'Content-Type: text/plain; charset=UTF-8');

$sendEmail = false;
$quiet = false;
foreach ($argv as $i => $arg) {
  if ($i) {
    switch ($arg) {
    case '--send-email': $sendEmail = true; break;
    case '--quiet': $quiet = true; break;
    default: print "Unknown flag $arg -- aborting\n"; exit;
    }
  }
}

$staticFiles = file(Config::get('static.url') . 'fileList.txt');
$messages = array();
$firstErrorDate = null;

for ($d = 0; $d <= NUM_DAYS; $d++) {
  $date = date("Y-m-d", strtotime("+{$d} days"));

  // Check that exactly one WotD exists
  $wotds = WordOfTheDay::get_all_by_displayDate($date);
  if (count($wotds) != 1) {
    addError($date, count($wotds) ? sprintf("Există %s cuvinte", count($wotds)) : "Nu există niciun cuvânt");
    continue;
  }
  $wotd = $wotds[0];

  // Check that it has exactly one WotD rel
  $rels = WordOfTheDayRel::get_all_by_wotdId($wotd->id);
  if (count($rels) != 1) {
    addError($date, count($rels) ? sprintf("Există %s definiții asociate", count($rels)) : "Nu există nicio definiție asociată");
    continue;
  }
   
  // Check that the definition exists
  $def = Definition::get_by_id($rels[0]->refId);
  if (!$def) {
    addError($date, sprintf("Definiția cu id-ul %s nu există", $rels[0]->refId));
    continue;
  }

  // Check that we haven't had the same word or a similar one in the past.
  // Currently we look for words that contain, or are contained by, the proposed word.
  $query = sprintf("select d.lexicon, w.displayDate " .
                   "from WordOfTheDay w, WordOfTheDayRel r, Definition d " .
                   "where w.id = r.wotdId " .
                   "and r.refId = d.id " .
                   "and r.refType = 'Definition' " .
                   "and w.displayDate < '%s' " .
                   "and char_length(lexicon) >= 4 " .
                   "and ((instr('%s', lexicon) > 0) or (lexicon like '%%%s%%'))",
                   $date, $def->lexicon, $def->lexicon);
  $dups = DB::getArrayOfRows($query);
  if (count($dups)) {
    $msg = "Cuvântul {$def->lexicon} seamănă cu următoarele cuvinte deja propuse:";
    foreach ($dups as $dup) {
      $msg .= " {$dup[0]} ({$dup[1]})";
    }
    addInfo($date, $msg);
  }

  // Check that there is an artist
  $artist = WotdArtist::getByDate($date);
  if (!$artist) {
    addError($date, 'Niciun artist nu este asignat; asignați un artist la https://dexonline.ro/alocare-autori');
  }

  // Check that there is an image
  if (!$wotd->image) {
    $assignedImage = assignImageByName($wotd, $def);
    if ($assignedImage) {
      $wotd->image = $assignedImage;
      $wotd->save();
      addInfo($date, "Am asociat definiția '{$def->lexicon}' cu imaginea {$assignedImage}");
    } else {
      addError($date, sprintf("Definiția '%s' nu are o imagine asociată (motivul alegerii: %s)", $def->lexicon, $wotd->description));
      if ($artist && !in_array($artist->email, $rcptError)) {
        $rcptError[] = $artist->email;
      }
      continue;
    }
  }

  // Check that the image file exists
  if (!$wotd->imageExists()) {
    addError($date, sprintf("Definiția '%s' are imaginea asociată '%s', dar fișierul nu există", $def->lexicon, $wotd->image));
    continue;
  }
}

Log::info("checkWotd: collected " . count($messages) . " messages");
if (count($messages)) {
  if ($firstErrorDate) {
    $today = date("Y-m-d", strtotime("today"));
    $days = daysBetween($today, $firstErrorDate);
    switch ($days) {
    case 0: 
    case 1: $subject = 'ACUM'; break;
    case 2: $subject = 'ASTĂZI'; break;
    case 3: $subject = 'cel târziu mâine'; break;
    default: $subject = sprintf("în %s zile", $days - 1);
    }
    $subject = 'Cuvântul zilei: acțiune necesară ' . $subject;
    $mailTo = array_merge($rcptInfo, $rcptError);
  } else {
    $subject = 'Cuvântul zilei: notă informativă';
    $mailTo = $rcptInfo;
  }
  $mailTo = implode(', ', $mailTo);

  SmartyWrap::assign('numDays', NUM_DAYS);
  SmartyWrap::assign('messages', $messages);
  $body = SmartyWrap::fetch('email/checkWotd.tpl');
  if ($sendEmail) {
    Log::info("checkWotd: sending email");
    mail($mailTo, $subject, $body, implode("\r\n", $MAIL_HEADERS));
  } else if (!$quiet) {
    print "---- DRY RUN ----\n";
    print "Către: $mailTo\nSubiect: $subject\n\n$body\n";
  }
}

Log::notice('finished');

/*********************************************************************/

function addError($date, $text) {
  global $firstErrorDate;
  addMessage('eroare', $date, $text);
  if (!$firstErrorDate || $date < $firstErrorDate) {
    $firstErrorDate = $date;
  }
}

function addInfo($date, $text) {
  addMessage('info', $date, $text);
}

function addMessage($type, $date, $text) {
  global $messages;

  $messages[] = array('type' => $type, 'date' => $date, 'text' => $text);
  Log::info("checkWotd: adding message [$type] [$date] [$text]");
}

function daysBetween($date1, $date2) {
  $d1 = new DateTime($date1);
  $d2 = new DateTime($date2);
  return $d2->diff($d1)->days;
}

/**
 * Attempts to assign an image automatically for the given date and word.
 * Returns the image name on success, null on failure
 **/
function assignImageByName($wotd, $def) {
  global $staticFiles;

  $year = substr($wotd->displayDate, 0, 4);
  $month = substr($wotd->displayDate, 5, 2);
  $strippedLexicon = stripImageName($def->lexicon);
  foreach ($staticFiles as $file) {
    if (StringUtil::startsWith($file, "img/wotd/{$year}/{$month}/")) {
      $file = basename(trim($file));
      $strippedFile = stripImageName($file);
      if (preg_match("/{$strippedLexicon}\\.(png|jpg|jpeg)/", $strippedFile)) {
        return "{$year}/{$month}/{$file}";
      }
    }
  }
  return null;
}

// Convert to Latin-1 and strip '-' and ' '
function stripImageName($fileName) {
  $s = StringUtil::unicodeToLatin($fileName);
  $s = str_replace(array('-', ' ', 'ş', 'ţ', 'Ş', 'Ţ'), array('', '', 's', 't', 's', 't'), $s);
  $s = mb_strtolower($s);
  return $s;
}

?>
