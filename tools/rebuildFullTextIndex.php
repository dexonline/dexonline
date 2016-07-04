<?php
require_once __DIR__ . '/../phplib/util.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '512M');
assert_options(ASSERT_BAIL, 1);
ORM::get_db()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

Log::notice('started');
if (!Lock::acquire(LOCK_FULL_TEXT_INDEX)) {
  OS::errorAndExit('Lock already exists!');
  exit;
}

Log::info("Clearing table FullTextIndex.");
db_execute('truncate table FullTextIndex');

// Build a map of stop words
$stopWordForms = array_flip(db_getArray(
  'select distinct i.formNoAccent ' .
  'from Lexem l, LexemModel lm, InflectedForm i ' .
  'where l.id = lm.lexemId ' .
  'and lm.id = i.lexemModelId ' .
  'and l.stopWord'));

// Build a map of inflectedForm => list of (lexemModelId, inflectionId) pairs
Log::info("Building inflected form map.");
$dbResult = db_execute("select formNoAccent, lexemModelId, inflectionId from InflectedForm");
$ifMap = [];
foreach ($dbResult as $r) {
  $form = $r['formNoAccent'];
  $s = isset($ifMap[$form])
     ? ($ifMap[$form] . ',')
     : '';
  $s .= $r['lexemModelId'] . ',' . $r['inflectionId'];
  $ifMap[$form] = $s;
}
unset($dbResult);
Log::info("Inflected form map has %d entries.", count($ifMap));
Log::info("Memory used: %d MB", round(memory_get_usage() / 1048576, 1));

// Process definitions
$dbResult = db_execute('select id, internalRep from Definition where status = 0');
$defsSeen = 0;
$indexSize = 0;
$fileName = tempnam(Config::get('global.tempDir'), 'index_');
$handle = fopen($fileName, 'w');
Log::info("Writing index to file $fileName.");
DebugInfo::disable();

foreach ($dbResult as $dbRow) {
  $words = extractWords($dbRow[1]);

  foreach ($words as $position => $word) {
    if (!isset($stopWordForms[$word])) {
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
    Log::info("$defsSeen definitions indexed ($speed defs/sec). ");
  }
}
unset($dbResult);

fclose($handle);
Log::info("$defsSeen definitions indexed.");
Log::info("Index size: $indexSize entries.");

OS::executeAndAssert("chmod 666 $fileName");
Log::info("Importing file $fileName into table FullTextIndex");
db_executeFromOS("load data local infile \"$fileName\" into table FullTextIndex");
util_deleteFile($fileName);

if (!Lock::release(LOCK_FULL_TEXT_INDEX)) {
  Log::warning('WARNING: could not release lock!');
}
Log::notice('finished; peak memory usage %d MB', round(memory_get_usage() / 1048576, 1));

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

?>
