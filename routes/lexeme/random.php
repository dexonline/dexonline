<?php

const MIN_COUNT = 10;
const MAX_COUNT = 2500;
const DEFAULT_COUNT = 100;

const DEFAULT_SHOW_SOURCE = false;
const DEFAULT_SHOW_SKIN = true;
const SOURCE_PART = ', surse';

const QUERY =
  'select cuv %s ' .
  'from RandomWord ' .
  'where seq in (%s) ';

$format = Request::getFormat();

$wordCount = Model::factory('RandomWord')->count();

switch ($format['name']) {
  case 'xml':
  case 'json':
    displayJsonXml($format, $wordCount);
    break;
  default:
    displayHtml();
}

/**
 * Displays on site, with the default skin
 *
 * @param none no need
 */
function displayHtml() {
  $displayCount = Request::getInRange('count', DEFAULT_COUNT, MIN_COUNT, MAX_COUNT);

  $showSource = Request::get('source', DEFAULT_SHOW_SOURCE); // TODO - looks unused
  $skin = Request::get('skin', DEFAULT_SHOW_SKIN); // TODO - looks unused

  $query = sprintf(QUERY, $showSource ? SOURCE_PART : '', generateRandomSequence($displayCount)); // TODO -  $showSource looks unused
  $title = sprintf(ngettext(
    'A randomly chosen word',
    '%d randomly chosen words',
    $displayCount), $displayCount);
  $forms = DB::getArrayOfRows($query);

  Smart::assign([
    'forms' => $forms,
    'title' => $title,
  ]);
  if ($skin) {
    Smart::display('aggregate/randomWords.tpl');
  } else {
    Smart::displayWithoutSkin('bits/randomWordsSimple.tpl'); // TODO - looks unused
  }
}

/**
 * Ignores $_REQUEST['count']
 * Displays only one word and one corresponding randomly selected definition
 * through JSON or XML, obeying the format requested in URI
 * random-words/100/json or
 * random-words/json
 *
 * Same applies to xml
 *
 * @param format format to displat json/xml
 * @param wordcount counted word in RandomWord table
 */
function displayJsonXml($format, $wordCount) {
  // The seq field is guaranteed to be incremental from 1 to <number of rows>
  $choice = mt_rand(1, $wordCount);
  $rw = RandomWord::get_by_seq($choice);
  $ids = explode(',', $rw->sourceIds);
  shuffle($ids);

  $rndSourceId = array_shift($ids);

  // serving in JSON or XML
  // some advanced search needed
  $result = Model::factory('Definition')
    ->table_alias('d')
    ->select("d.*")
    ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
    ->join('Entry', ['ed.entryId', '=', 'e.id'], 'e')
    ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
    ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l')
    ->where('l.formNoAccent', $rw->cuv)
    ->where('d.sourceId', $rndSourceId)
    ->find_one();
  $searchResults = SearchResult::mapDefinitionArray([$result]);

  Smart::assign([
    'row'=> array_pop($searchResults),
  ]);
  header('Content-type: '.$format['content_type']);
  Smart::displayWithoutSkin($format['tpl_path'].'/randomWords.tpl');

}


/*************************************************************************/

function generateRandomSequence($count) {
  $seq = [];
  for ($i = 1; $i <= $count; $i++) {
    $seq[] = mt_rand(0, 184415);
  }
  $ret = implode(',', $seq);
  return $ret;
}
