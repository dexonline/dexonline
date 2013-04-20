<?php
require_once('../phplib/util.php');
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '256M');
assert_options(ASSERT_BAIL, 1);

log_scriptLog('Running rebuildFullTextIndex.php.');
if (!Lock::acquire(LOCK_FULL_TEXT_INDEX)) {
  OS::errorAndExit('Lock already exists!');
  exit;
}

log_scriptLog("Clearing table FullTextIndex.");
db_execute('truncate table FullTextIndex');

$ifMap = array();
$dbResult = db_execute('select id, internalRep from Definition where status = 0');
$numDefs = $dbResult->rowCount();
$defsSeen = 0;
$indexSize = 0;
$fileName = tempnam('/tmp', 'index_');
$handle = fopen($fileName, 'w');
log_scriptLog("Writing index to file $fileName.");
DebugInfo::disable();

foreach ($dbResult as $dbRow) {
  $words = extractWords($dbRow[1]);

  foreach ($words as $position => $word) {
    if (StringUtil::isStopWord($word, true)) {
      // Nothing, this word is ignored.
    } else {
      if (!array_key_exists($word, $ifMap)) {
        cacheWordForm($word);
      }
      if (array_key_exists($word, $ifMap)) {
        $lexemList = preg_split('/,/', $ifMap[$word]);
        for ($i = 0; $i < count($lexemList); $i += 2) {
          fwrite($handle, $lexemList[$i] . "\t" . $lexemList[$i + 1] . "\t" . $dbRow[0] . "\t" . $position . "\n");
          $indexSize++;
        }
      } else {
        // print "Not found: $word\n";
      }
    }
  }

  if (++$defsSeen % 10000 == 0) {
    $runTime = DebugInfo::getRunningTimeInMillis() / 1000;
    $speed = round($defsSeen / $runTime);
    log_scriptLog("$defsSeen of $numDefs definitions indexed ($speed defs/sec). " .
                  "Word map has " . count($ifMap) . " entries. " .
                  "Memory used: " . round(memory_get_usage() / 1048576, 1) . " MB.");
  }
}

fclose($handle);
log_scriptLog("$defsSeen of $numDefs definitions indexed.");
log_scriptLog("Index size: $indexSize entries.");

OS::executeAndAssert("chmod 666 $fileName");
log_scriptLog("Importing file $fileName into table FullTextIndex");
db_executeFromOS("load data local infile '$fileName' into table FullTextIndex");
util_deleteFile($fileName);

if (!Lock::release(LOCK_FULL_TEXT_INDEX)) {
  log_scriptLog('WARNING: could not release lock!');
}
log_scriptLog('rebuildFullTextIndex.php completed successfully ' .
              '(against all odds)');

/***************************************************************************/

function extractWords($text) {
  $alphabet = 'abcdefghijklmnopqrstuvwxyzăâîșț';

  $text = mb_strtolower($text);
  $text = AdminStringUtil::removeAccents($text);
  $result = array();

  $currentWord = '';
  $chars = AdminStringUtil::unicodeExplode($text);
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

function cacheWordForm($word) {
  global $ifMap;
  $dbResult = db_execute("select lexemId, inflectionId from InflectedForm where formNoAccent = '$word'");
  $value = '';
  foreach ($dbResult as $dbRow) {
    $value .= ',' . $dbRow['lexemId'] . ',' . $dbRow['inflectionId'];
  }
  if ($value) {
    $ifMap[$word] = substr($value, 1);
  }
}

?>
