<?php
require_once("../phplib/util.php");

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

$wListLength = (int) util_getRequestParameter('w');
if (is_int($wListLength) && $wListLength) {
    if ($wListLength<=MIN_WOTD_LIST_LENGTH || $wListLength>MAX_WOTD_LIST_LENGTH) {
        $wListLength = DEFAULT_WOTD_LIST_LENGTH;
    }
}
else {
    $wListLength = NULL;
}


$listLength = (int) util_getRequestParameter('n');
if (!is_int($listLength) || $listLength<=MIN_LIST_LENGTH || $listLength>MAX_LIST_LENGTH) {
    $listLength = DEFAULT_LIST_LENGTH;
}

$showSource = (int) util_getRequestParameter('s');
if ( !is_int($showSource) || $showSource!=1 ){
    $showSource = DEFAULT_SHOW_LIST;
}

$noSkin = (int) util_getRequestParameter('k');
if ( !is_int($noSkin) || $noSkin!=1 ){
    $noSkin = DEFAULT_SHOW_LIST;
}

/*
$query = sprintf('select id from Lexem order by rand() limit %d', $listLength);
$ids = db_getArray($query);

$query = sprintf(RANDOM_WORDS_QUERY, $showSource?SOURCE_PART_RANDOM_WORDS:'', implode(",",$ids));
$forms = db_getArrayOfRows($query);
*/

$wotd = '';
if (is_null($wListLength)) {
    $query = sprintf(RANDOM_WORDS_QUERY, $showSource?SOURCE_PART_RANDOM_WORDS:'', $listLength);
}
else {
    $query = sprintf(RANDOM_WOTD_QUERY, $wListLength);
    $wotd = ' ale zilei';
}
$forms = db_getArrayOfRows($query);

$cnt = count($forms);

if ($noSkin) {
    SmartyWrap::assign('forms', $forms);
    SmartyWrap::displayWithoutSkin('randomWordListSimple.ihtml');
}
else {
    SmartyWrap::assign('forms', $forms);
    SmartyWrap::assign('page_title', "O listă de {$cnt} de cuvinte{$wotd} alese la întâmplare.");
    SmartyWrap::display('randomWordList.ihtml');
}
?>
