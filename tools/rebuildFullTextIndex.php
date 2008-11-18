<?
require_once('../phplib/util.php');
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '1024000000');
assert_options(ASSERT_BAIL, 1);

log_scriptLog('Running rebuildFullTextIndex.php.');
if (!lock_acquire(LOCK_FULL_TEXT_INDEX)) {
  log_scriptLog('Exiting: lock already exists!');
  exit;
}


log_scriptLog("Clearing table FullTextIndex.");
mysql_query('delete from FullTextIndex');

// Build a map of wordlist -> (lexemId, inflectionId)
$wlMap = buildWordMap();

$dbResult = mysql_query('select * from Definition where Status = 0');
$numDefs = mysql_num_rows($dbResult);
$defsSeen = 0;
$indexSize = 0;
$fileName = tempnam('/tmp', 'index_');
$handle = fopen($fileName, 'w');
log_scriptLog("Writing index to file $fileName.");
debug_init();
debug_off();

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $def = Definition::createFromDbRow($dbRow);
  $words = extractWords($def->internalRep);

  foreach ($words as $position => $word) {
    if (text_isStopWord($word, true)) {
      // Nothing, this word is ignored.
    } else if (array_key_exists($word, $wlMap)) {
      $lexemList = $wlMap[$word];
      foreach ($lexemList as $pair) {
        fwrite($handle, $pair[0] . "\t" . $pair[1] . "\t" .
               $def->id . "\t" . $position . "\n");
        $indexSize++;
      }
    } else {
      // print "Not found: $word\n";
    }
  }

  if (++$defsSeen % 10000 == 0) {
     $runTime = debug_getRunningTimeInMillis() / 1000;
     $speed = round($defsSeen / $runTime);
     log_scriptLog("$defsSeen of $numDefs definitions indexed " .
                   "($speed defs/sec)");
  }
}

fclose($handle);
log_scriptLog("$defsSeen of $numDefs definitions indexed.");
log_scriptLog("Index size: $indexSize entries.");

os_executeAndAssert("chmod 666 $fileName");
log_scriptLog("Importing file $fileName into table FullTextIndex");
mysql_query("load data infile '$fileName' into table FullTextIndex");

if (!lock_release(LOCK_FULL_TEXT_INDEX)) {
  log_scriptLog('WARNING: could not release lock!');
}
log_scriptLog('rebuildFullTextIndex.php completed successfully ' .
              '(against all odds)');

/***************************************************************************/

function extractWords($text) {
  $alphabet = 'abcdefghijklmnopqrstuvwxyzăâîşţ';

  $text = text_unicodeToLower($text);
  $text = text_removeAccents($text);
  $result = array();

  $currentWord = '';
  $chars = text_unicodeExplode($text);
  foreach ($chars as $c) {
    if (strpos($alphabet, $c) !== false) {
      $currentWord .= $c;
    } else {
      if ($currentWord) {
        $result[] = $currentWord;
      }
      $currentWord = '';
    }
  }

  if ($currentWord) {
    $result[] = $currentWord;
  }

  return $result;
}

function buildWordMap() {
  $wlMap = array();
  $dbResult = mysql_query('select wl_neaccentuat, wl_lexem, wl_analyse ' .
                          'from wordlist');
  $numWordLists = mysql_num_rows($dbResult);
  log_scriptLog('Caching $numWordLists word forms.');
  $seen = 0;
  while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
    $form = $dbRow['wl_neaccentuat'];
    $lexemId = $dbRow['wl_lexem'];
    $inflectionId = $dbRow['wl_analyse'];
    $value = array($lexemId, $inflectionId);
    if (array_key_exists($form, $wlMap)) {
      $wlMap[$form][] = $value;
    } else {
      $wlMap[$form] = array($value);
    }
    $seen++;
    if ($seen % 10000 == 0) {
      log_scriptLog("$seen word forms cached...");
    }
  }
  log_scriptLog("$numWordLists word forms cached.");
  return $wlMap;
}

?>
