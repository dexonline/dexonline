<?php
/**
 * Output two lists of words associated with some common dictionaries, once with diacritical
 * marks and once without them.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('STATIC_SERVER_DIR', '/download/scrabble');

Log::notice('started');

$locVersions = Config::getLocVersions();
$lv = $locVersions[1]->name; // use the last frozen one
LocVersion::changeDatabase($lv);
$tempDir = Core::getTempPath();

Log::info('collecting forms');
$forms = Model::factory('InflectedForm')
       ->table_alias('i')
       ->select('i.formNoAccent')
       ->distinct()
       ->join('Lexem', ['i.lexemId', '=', 'l.id'], 'l')
       ->join('ModelType', ['l.modelType', '=', 'mt.code'], 'mt')
       ->join('Model', 'mt.canonical = m.modelType and l.modelNumber = m.number', 'm')
       ->join('ModelDescription',
              'm.id = md.modelId and i.variant = md.variant and i.inflectionId = md.inflectionId',
              'md')
       ->where('l.isLoc', 1)
       ->where('md.isLoc', 1)
       ->where_raw('binary i.formNoAccent rlike "^[a-zăâîșț]+$"') // no caps - chemical symbols etc.
       ->where_raw('char_length(i.formNoAccent) between 3 and 7')
       ->order_by_asc('i.formNoAccent')
       ->find_many();
$joined = implode("\n", Util::objectProperty($forms, 'formNoAccent'));

$diaFileName = $tempDir.'/game-word-list-dia.txt';
Log::info('writing forms to %s', $diaFileName);
file_put_contents($diaFileName, $joined);

$tmpFileName = $tempDir.'/game-word-list-tmp.txt';
$noDiaFileName = $tempDir.'/game-word-list.txt';
Log::info('writing Latin forms to %s', $noDiaFileName);
$latin = Str::unicodeToLatin($joined);
file_put_contents($tmpFileName, $latin);
exec("sort $tmpFileName | uniq > $noDiaFileName");

Log::info('uploading files to static server');
$f = new FtpUtil();
$f->staticServerPut($diaFileName, 'download/game-word-list-dia.txt');
$f->staticServerPut($noDiaFileName, 'download/game-word-list.txt');

// cleanup
unlink($diaFileName);
unlink($tmpFileName);
unlink($noDiaFileName);

Log::notice('finished');
