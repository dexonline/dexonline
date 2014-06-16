<?php
/**
 * This script regenerates the scrabble form list (unique, no accents / diacritics, between 2 and 15 characters).
 **/

require_once __DIR__ . '/../phplib/util.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '128M');

log_scriptLog('rebuildScrabbleForms: starting');

$opts = getopt('a');
$allVersions = array_key_exists('a', $opts);

foreach (Config::getLocVersions() as $version) {
  if (!$version->freezeTimestamp || $allVersions) {
    log_scriptLog("dumping version {$version->name}");
    LocVersion::changeDatabase($version->name);

    log_scriptLog('* running ginormous query');
    $dbResult = getRawForms();

    log_scriptLog('* creating raw file');
    $fileName = "/tmp/forme-{$version->name}-raw.txt";
    $file = fopen($fileName, 'w');
    foreach ($dbResult as $dbRow) {
      fwrite($file, "{$dbRow[0]}\r\n");
    }
    fclose($file);

    log_scriptLog('* removing diacritics, converting to uppercase');
    $s = file_get_contents($fileName);
    $s = StringUtil::unicodeToLatin($s);
    $s = strtoupper($s);
    file_put_contents($fileName, $s);

    log_scriptLog('* removing duplicates and sorting');
    $fileName2 = "/tmp/forme-{$version->name}.txt";
    OS::executeAndAssert("sort -u {$fileName} -o {$fileName2}");

    log_scriptLog('* zipping');
    $zipFileName = "/tmp/forme-{$version->name}.zip";
    OS::executeAndAssert("zip -j {$zipFileName} {$fileName2}");

    log_scriptLog('* copying over FTP');
    FtpUtil::staticServerPut($zipFileName, "/download/forme-{$version->name}.zip");

    unlink($zipFileName);
    unlink($fileName);
    unlink($fileName2);
  }
}

log_scriptLog('rebuildScrabbleForms: ending');

/***************************************************************************/

function getRawForms() {
  $query = 'select I.formNoAccent ' .
    'from InflectedForm I, LexemModel LM, Lexem L, Model M, ModelDescription MD, ModelType MT ' .
    'where I.lexemModelId = LM.id ' .
    'and LM.lexemId = L.id ' .
    'and LM.modelType = MT.code ' .
    'and MT.canonical = M.modelType ' .
    'and LM.modelNumber = M.number ' .
    'and M.id = MD.modelId ' .
    'and MD.inflectionId = I.inflectionId ' .
    'and MD.variant = I.variant ' .
    'and MD.applOrder = 0 ' .
    'and LM.isLoc and MD.isLoc ' .
    'and char_length(I.formNoAccent) between 2 and 15';
  return db_execute($query);
}

?>
