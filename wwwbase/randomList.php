<?php
require_once("../phplib/Core.php");

const MIN_LIST_LENGTH = 10;
const MAX_LIST_LENGTH = 2500;
const DEFAULT_LIST_LENGTH = 100;
const DEFAULT_SHOW_LIST = 0;

const MIN_WOTD_LIST_LENGTH = 5;
const MAX_WOTD_LIST_LENGTH = 50;
const DEFAULT_WOTD_LIST_LENGTH = 20;

//const RANDOM_WORDS_QUERY = 'select cuv %s from RandomWord where id in (%s)';
const RANDOM_WORDS_QUERY = 'select cuv %s from RandomWord order by rand() limit %d';
const RANDOM_WOTD_QUERY = 'select lexicon cuv from WordOfTheDay W join Definition D on W.definitionId=D.id order by rand() limit %d';
const SOURCE_PART_RANDOM_WORDS = ', surse';

$wListLength = (int) Request::get('w');
if (is_int($wListLength) && $wListLength) {
  if ($wListLength < MIN_WOTD_LIST_LENGTH || $wListLength > MAX_WOTD_LIST_LENGTH) {
    $wListLength = DEFAULT_WOTD_LIST_LENGTH;
  }
} else {
  $wListLength = NULL;
}

$listLength = (int) Request::get('n');
if (!is_int($listLength) || $listLength < MIN_LIST_LENGTH || $listLength > MAX_LIST_LENGTH) {
  $listLength = DEFAULT_LIST_LENGTH;
}

$showSource = (int) Request::get('s');
if ( !is_int($showSource) || $showSource!=1 ){
  $showSource = DEFAULT_SHOW_LIST;
}

$noSkin = (int) Request::get('k');
if ( !is_int($noSkin) || $noSkin!=1 ){
  $noSkin = DEFAULT_SHOW_LIST;
}

/*
  $query = sprintf('select id from Lexeme order by rand() limit %d', $listLength);
  $ids = DB::getArray($query);

  $query = sprintf(RANDOM_WORDS_QUERY, $showSource?SOURCE_PART_RANDOM_WORDS:'', implode(",",$ids));
  $forms = DB::getArrayOfRows($query);
*/

if (is_null($wListLength)) {
  $query = sprintf(RANDOM_WORDS_QUERY, $showSource?SOURCE_PART_RANDOM_WORDS:'', $listLength);
  $title = sprintf(ngettext(
    'A randomly chosen word',
    '%d randomly chosen words',
    $listLength), $listLength);
} else {
  $query = sprintf(RANDOM_WOTD_QUERY, $wListLength);
  $title = sprintf(ngettext(
    'A randomly chosen word of the day',
    '%d randomly chosen words of the day',
    $wListLength), $wListLength);
}
$forms = DB::getArrayOfRows($query);

$cnt = count($forms);

SmartyWrap::assign([
  'forms' => $forms,
  'title' => $title,
]);
if ($noSkin) {
  SmartyWrap::displayWithoutSkin('bits/randomWordListSimple.tpl');
} else {
  SmartyWrap::display('randomWordList.tpl');
}
