<?php
require_once __DIR__ . '/../phplib/util.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '256M');
assert_options(ASSERT_BAIL, 1);

define('BATCH_SIZE', 10000);

Log::notice('started');
if (!Lock::acquire(LOCK_FULL_TEXT_INDEX)) {
  OS::errorAndExit('Lock already exists!');
  exit;
}

Log::info("Clearing table FullTextIndex.");
db_execute('truncate table FullTextIndex');

$stopWordForms = array_flip(db_getArray(
  'select distinct i.formNoAccent ' .
  'from Lexem l, InflectedForm i ' .
  'where l.id = i.lexemId ' .
  'and l.stopWord'));

$ifMap = [];
$offset = 0;
$indexSize = 0;
$fileName = tempnam(Config::get('global.tempDir'), 'index_');
$handle = fopen($fileName, 'w');
Log::info("Writing index to file $fileName.");
DebugInfo::disable();

do {
  $defs = Model::factory('Definition')
        ->select('id')
        ->select('internalRep')
        ->where('status', Definition::ST_ACTIVE)
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_array();

  foreach ($defs as $d) {
    $words = extractWords($d['internalRep']);

    foreach ($words as $position => $word) {
      if (!isset($stopWordForms[$word])) {
        if (!array_key_exists($word, $ifMap)) {
          cacheWordForm($word);
        }
        if (array_key_exists($word, $ifMap)) {
          $lexemList = preg_split('/,/', $ifMap[$word]);
          for ($i = 0; $i < count($lexemList); $i += 2) {
            fwrite($handle, $lexemList[$i] . "\t" . $lexemList[$i + 1] . "\t" . $d['id'] . "\t" . $position . "\n");
            $indexSize++;
          }
        } else {
          // print "Not found: $word\n";
        }
      }
    }
  }

  $offset += BATCH_SIZE;
  $runTime = DebugInfo::getRunningTimeInMillis() / 1000;
  $speed = round($offset / $runTime);
  Log::info("$offset definitions indexed ($speed defs/sec). " .
            "Word map has " . count($ifMap) . " entries. " .
            "Memory used: " . round(memory_get_usage() / 1048576, 1) . " MB.");
} while (count($defs) == BATCH_SIZE);

fclose($handle);
Log::info("Index size: $indexSize entries.");

OS::executeAndAssert("chmod 666 $fileName");
Log::info("Importing file $fileName into table FullTextIndex");
db_executeFromOS("load data local infile \"$fileName\" into table FullTextIndex");
util_deleteFile($fileName);

if (!Lock::release(LOCK_FULL_TEXT_INDEX)) {
  Log::warning('WARNING: could not release lock!');
}
Log::notice('finished');

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

// Look up all lexems that generate this word form and that are not stop words
function cacheWordForm($word) {
  global $ifMap;
  $dbResult = db_execute("select lexemId, inflectionId from InflectedForm where formNoAccent = '{$word}'");
  $value = '';
  foreach ($dbResult as $dbRow) {
    $value .= ',' . $dbRow['lexemId'] . ',' . $dbRow['inflectionId'];
  }
  if ($value) {
    $ifMap[$word] = substr($value, 1);
  }
}

?>
