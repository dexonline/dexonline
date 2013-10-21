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
    $query = 'select I.formNoAccent from InflectedForm I, Lexem L, Model M, ModelDescription MD, ModelType MT ' .
      'where I.lexemId = L.id and L.modelType = MT.code and MT.canonical = M.modelType and L.modelNumber = M.number and M.id = MD.modelId ' .
      'and MD.inflectionId = I.inflectionId and MD.variant = I.variant and MD.applOrder = 0 and L.isLoc and MD.isLoc ' .
      'and char_length(I.formNoAccent) between 2 and 15';
    $dbResult = db_execute($query);

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
    $file = fopen($fileName, 'w');
    fwrite($file, $s);
    fclose($file);

    log_scriptLog('* removing duplicates and sorting');
    $fileName2 = "/tmp/forme-{$version->name}.txt";
    OS::executeAndAssert("sort -u {$fileName} -o {$fileName2}");

    log_scriptLog('* zipping');
    $destFileName = util_getRootPath() . "wwwbase/download/forme-{$version->name}.zip";
    @unlink($destFileName);
    OS::executeAndAssert("zip -j {$destFileName} {$fileName2}");

    unlink($fileName);
    unlink($fileName2);
  }
}

log_scriptLog('rebuildScrabbleForms: ending');

?>
