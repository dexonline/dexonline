<?php
/**
 * This script checks whether exactly one WotD is set and whether it has an image, for the upcoming N days.
 **/

require_once __DIR__ . '/../phplib/util.php';

define('NUM_DAYS', 7);
$MAIL_TO = 'cata@dexonline.ro, doru@dexonline.ro, radu@dexonline.ro';
$MAIL_HEADERS = array('From: cata@dexonline.ro', 'Reply-To: cata@dexonline.ro');

$sendEmail = false;
foreach ($argv as $i => $arg) {
  if ($i) {
    switch ($arg) {
    case '--send-email': $sendEmail = true; break;
    default: print "Unknown flag $arg -- ignored\n"; exit;
    }
  }
}
if (!$sendEmail) {
  print "---- DRY RUN ----\n";
}

$messages = array();
$firstProblem = 0;

for ($d = 0; $d <= NUM_DAYS; $d++) {
  $date = date("Y-m-d", strtotime("+{$d} days"));

  // Check that exactly one WotD exists
  $wotds = WordOfTheDay::get_all_by_displayDate($date);
  if (count($wotds) != 1) {
    $messages[$date] = count($wotds)
      ? sprintf("Există %s cuvinte", count($wotds))
      : "Nu există niciun cuvânt";
    continue;
  }

  // Check that it has exactly one WotD rel
  $rels = WordOfTheDayRel::get_all_by_wotdId($wotds[0]->id);
  if (count($rels) != 1) {
    $messages[$date] = count($rels)
      ? sprintf("Există %s definiții asociate", count($rels))
      : "Nu există nicio definiție asociată";
    continue;
  }
   
  // Check that the definition exists
  $def = Definition::get_by_id($rels[0]->refId);
  if (!$def) {
    $messages[$date] = sprintf("Definiția cu id-ul %s nu există", $rels[0]->refId);
    continue;
  }

  // Check that there is an image
  if (!$wotds[0]->image) {
    $messages[$date] = sprintf("Definiția '%s' nu are o imagine asociată", $def->lexicon);
    continue;
  }

  // Check that the image file exists
  if (!$wotds[0]->imageFileExists()) {
    $messages[$date] = sprintf("Definiția '%s' are imaginea asociată '%s', dar fișierul nu există", $def->lexicon, $wotds[0]->image);
    continue;
  }

  if ($firstProblem == $d) {
    $firstProblem++;
  }
}

if ($messages) {
  switch ($firstProblem) {
  case 0: $subject = 'ACUM'; break;
  case 1: $subject = 'ASTĂZI'; break;
  case 2: $subject = 'cel târziu mâine'; break;
  default: $subject = sprintf("în %s zile", $firstProblem - 1);
  }
  $subject = 'Cuvântul zilei: acțiune necesară ' . $subject;

  smarty_assign('numDays', NUM_DAYS);
  smarty_assign('messages', $messages);
  $body = smarty_fetch('email/checkWotd.ihtml');
  if ($sendEmail) {
    mail($MAIL_TO, $subject, $body, implode("\n", $MAIL_HEADERS));
  } else {
    print "Subiect: $subject\n\n$body\n";
  }
}

?>
