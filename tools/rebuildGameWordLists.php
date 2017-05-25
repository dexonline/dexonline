<?php
/**
 * Output two lists of words associated with some common dictionaries, once with diacritical
 * marks and once without them.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('STATIC_SERVER_DIR', '/download/scrabble');
$SOURCES = ['dex', 'dex09', 'dex12', 'dex16', 'doom2', 'dor', 'mdn00'];

Log::notice('started');

Log::info('collecting forms');
$forms = Model::factory('InflectedForm')
       ->table_alias('i')
       ->select('i.formNoAccent')
       ->distinct()
       ->join('Lexem', ['i.lexemId', '=', 'l.id'], 'l')
       ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
       ->join('EntryDefinition', ['el.entryId', '=', 'ed.entryId'], 'ed')
       ->join('Definition', ['ed.definitionId', '=', 'd.id'], 'd')
       ->join('Source', ['d.sourceId', '=', 's.id'], 's')
       ->where_in('s.urlName', $SOURCES)
       ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
       ->where_raw('binary i.formNoAccent rlike "^[a-zăâîșț]+$"') // no caps - chemical symbols etc.
       ->where_raw('char_length(i.formNoAccent) between 2 and 7')
       ->order_by_asc('i.formNoAccent')
       ->find_many();
$joined = implode("\n", Util::objectProperty($forms, 'formNoAccent'));

$diaFileName = '/tmp/game-word-list-dia.txt';
Log::info('writing forms to %s', $diaFileName);
file_put_contents($diaFileName, $joined);

$tmpFileName = '/tmp/game-word-list-tmp.txt';
$noDiaFileName = '/tmp/game-word-list.txt';
Log::info('writing Latin forms to %s', $noDiaFileName);
$latin = StringUtil::unicodeToLatin($joined);
file_put_contents($tmpFileName, $latin);
exec("sort $tmpFileName | uniq > $noDiaFileName");

Log::info('uploading files to static server');
$f = new FtpUtil();
$f->staticServerPut($diaFileName, 'download/game-word-list-dia.txt');
$f->staticServerPut($noDiaFileName, 'download/game-word-list.txt');

Log::notice('finished');
