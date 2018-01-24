<?php
require_once("../phplib/Core.php");

define('MIN_LIST_LENGTH', 10);
define('MAX_LIST_LENGTH', 2500);
define('DEFAULT_LIST_LENGTH', 100);
define('DEFAULT_SHOW_LIST', 0);

define('MIN_WOTD_LIST_LENGTH', 5);
define('MAX_WOTD_LIST_LENGTH', 50);
define('DEFAULT_WOTD_LIST_LENGTH', 15);

//define('RANDOM_WORDS_QUERY', 'select cuv %s from RandomWord where id in (%s)');
define('RANDOM_WORDS_QUERY', 'select cuv %s from RandomWord order by rand() limit %d');
define('RANDOM_WOTD_QUERY', "select lexicon cuv from WordOfTheDayRel W join Definition D on W.refId=D.id and W.refType='Definition' order by rand() limit %d");
define('SOURCE_PART_RANDOM_WORDS', ', surse');

$wListLength = (int) Request::get('w');
if (is_int($wListLength) && $wListLength) {
  if ($wListLength<=MIN_WOTD_LIST_LENGTH || $wListLength>MAX_WOTD_LIST_LENGTH) {
    $wListLength = DEFAULT_WOTD_LIST_LENGTH;
  }
} else {
  $wListLength = NULL;
}

$listLength = (int) Request::get('n');
if (!is_int($listLength) || $listLength<=MIN_LIST_LENGTH || $listLength>MAX_LIST_LENGTH) {
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

$wotd = '';
if (is_null($wListLength)) {
  $query = sprintf(RANDOM_WORDS_QUERY, $showSource?SOURCE_PART_RANDOM_WORDS:'', $listLength);
} else {
  $query = sprintf(RANDOM_WOTD_QUERY, $wListLength);
  $wotd = ' ale zilei';
}
$forms = DB::getArrayOfRows($query);

$cnt = count($forms);

SmartyWrap::assign('forms', $forms);
SmartyWrap::assign('wotd', $wotd);
if ($noSkin) {
  SmartyWrap::displayWithoutSkin('bits/randomWordListSimple.tpl');
} else {
  SmartyWrap::display('randomWordList.tpl');
}
