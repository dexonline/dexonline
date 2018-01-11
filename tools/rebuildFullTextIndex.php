<?php
require_once __DIR__ . '/../phplib/Core.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '512M');
assert_options(ASSERT_BAIL, 1);
ORM::get_db()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

Log::notice('started');
if (!Lock::acquire(Lock::FULL_TEXT_INDEX)) {
  OS::errorAndExit('Lock already exists!');
}

Log::info("Clearing table FullTextIndex.");
DB::execute('truncate table FullTextIndex');

// Build a map of stop words
$stopWordForms = array_flip(DB::getArray(
  'select distinct i.formNoAccent ' .
  'from Lexem l, InflectedForm i ' .
  'where l.id = i.lexemId ' .
  'and l.stopWord'));

// Build a map of inflectedForm => list of (lexemId, inflectionId) pairs
Log::info("Building inflected form map.");
$dbResult = DB::execute("select formNoAccent, lexemId, inflectionId from InflectedForm");
$ifMap = [];
foreach ($dbResult as $r) {
  $form = $r['formNoAccent'];
  $s = isset($ifMap[$form])
     ? ($ifMap[$form] . ',')
     : '';
  $s .= $r['lexemId'] . ',' . $r['inflectionId'];
  $ifMap[$form] = $s;
}
unset($dbResult);
Log::info("Inflected form map has %d entries.", count($ifMap));
Log::info("Memory used: %d MB", round(memory_get_usage() / 1048576, 1));

// Process definitions
$dbResult = DB::execute('select id, internalRep from Definition where status = 0');
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
DB::executeFromOS("load data local infile \"$fileName\" into table FullTextIndex");
OS::deleteFile($fileName);

if (!Lock::release(Lock::FULL_TEXT_INDEX)) {
  Log::warning('WARNING: could not release lock!');
}
Log::notice('finished; peak memory usage %d MB', round(memory_get_peak_usage() / 1048576, 1));

/***************************************************************************/

function extractWords($text) {
  $alphabet = 'abcdefghijklmnopqrstuvwxyzăâîșț';

  $text = mb_strtolower($text);
  $text = StringUtil::removeAccents($text);

  // remove tonic accents (apostrophes not preceded by a backslash)
  $text = preg_replace("/(?<!\\\\)'/", '', $text);

  $result = [];
  $currentWord = '';
  $chars = StringUtil::unicodeExplode($text);
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
