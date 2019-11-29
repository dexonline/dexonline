<?php

require_once __DIR__ . '/../lib/Core.php';
ini_set("memory_limit","4G");

if (!Config::TAG_ID_REPROCESS) {
  print "Please define a REPROCESS tag then declare it's constant in Config.php !".PHP_EOL;
  exit;
}

const SOURCE_ID = 54;

$opts = getopt('anws');
$ambiguous = isset($opts['a']);
$dryRun = isset($opts['n']);
$writeToFile = isset($opts['w']);
$changeStatus = isset($opts['s']);

$rpr = new Reprocess();

if (empty($opts) ) { $rpr->readOptions($opts); }

$dbResult = $rpr->getDefinitionIds(SOURCE_ID);
$defCount = count($dbResult);

$count = 0;
$modified = 0;

foreach ($dbResult as $def) {
  $ambiguousMatches = [];
  $warningsSanitize = [];
  $newRep = $def->internalRep;

  // Remove existing hash signs if ambiguous option (-a) is set
  if ($ambiguous) {
    $regex = '/(?<!\\\\)#/m';
    $newRep = preg_replace($regex, '', $newRep);
  }

  // Replace accented letters with new tonic accent notation
  $regex = '/^(?:-)?(@)(.*)([\^_\{\d\}@])/mU';
  preg_match($regex, $newRep, $matches); // get just the first word in definition, if it's formatted bold

  // get the clean lexicon, without formatting
  $match = $matches[2]; // $matches[0] Full match, $matches[1] @, $matches[2] $lexicon, $matches[3] anything else
  $lexicon = Str::changeAccents($match); // changing to new format á = 'a

  $newRep = preg_replace_callback(
    $regex,
    function ($matches) use ($lexicon) {
      return $matches[1].$lexicon.$matches[3];
    },
    $newRep,
    1
  );

  $lex = Str::removeAccents($lexicon); // removing all kind of stress for testing equality

  if($lex === $lexicon) { // the same?
    // barbaric test!
    // probably we should look it up in Lexeme,
    // but many words have stress even though they are monosilabic
    if (mb_strlen($lex) > 2) {
      $lex = preg_replace('/[aeiouăâî\s]+/Ui', '', $lex); // strip vowels
      if (mb_strlen($lex) < mb_strlen($lexicon) - 1) { // not really a good test, but anyway
        $rpr->putApart($def->id, $rpr::WARN_STRESS, $lexicon, true);
      }
    }
  }

  // Changing punctuation
  $regexPunctuation = '/([,.;])(\$|@)/m';
  $newRep = preg_replace($regexPunctuation, '$2$1', $newRep);

  list($newRep, $ambiguousMatches) = Str::sanitize($newRep, $def->sourceId, $warningsSanitize);

  // Ambiguities are put aside for evaluation
  if (!empty($ambiguousMatches)) {
    if ($ambiguous) {
      $rpr->putApart($def->id, $rpr::WARN_AMBIGUOUS, $ambiguousMatches);
      $def->hasAmbiguousAbbreviations = 1;
    }
  }

  // Sanitization warnings may appear as solved or not
  if (!empty($warningsSanitize)) {

    if (isset($warningsSanitize[0][1]['chars'])) {
      $rpr->putApart($def->id, $rpr::MIXED_ALPH, implode($warningsSanitize[0][1]['chars']), true);
    } else {
      $rpr->putApart($def->id, $rpr::CONVERTED, $warningsSanitize[0][1]['stringTo']);
    }

  }

  // After all the processing, definition may have changed
  if ($newRep !== $def->internalRep) {
    // Finally, put mangled newRep as internalRep in $def
    $def->internalRep = $newRep;
    $modified++;
    $rpr->setModified($def->id);

    list($htmlRep, $ignored) = Str::htmlize($newRep, $def->sourceId, $errors, $warnings);

    if (!empty($errors)) {
      $rpr->putApart($def->id, $rpr::WARN_HTMLIZE, $warnings, true);
    }

  } else { // or not
      $rpr->putApart($def->id, $rpr::UNCONVERTED, $def->internalRep);
  }

  // For testing the output we put apart some defs
  if ($count % $rpr::MODULO_CHECK == 0) {
    $rpr->putApart($def->id, $rpr::MODULO_DEMO, $def->internalRep);
  }

  // Woof! Let's rock!
  if (!$dryRun){
    $saved = $def->save();
  }

  $count++; // another one bites the dust
  echo "Processed: " . Util::percentageOf($modified, $defCount, 0) . "% of " . $defCount . " definitions." . "\r";
}
print "$count definitions reprocessed, $modified modified." . ($dryRun ? "Warnings and errors follow" : "") .PHP_EOL;
$rpr->displayWarnings($writeToFile, $dryRun);

// tag last version of definition with TAG_ID_REPROCESS
if (!$dryRun) { 
  print "Wait a bit, tagging..." . "\r";
  $rpr->markReprocessTag(); 
}

// mark for review
if ($changeStatus) { 
  print "Wait a little more, mark pending..." . "\r";
  $rpr->markPending(); 
}
print "FINISHED!" .PHP_EOL;
