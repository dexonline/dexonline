<?
require_once("../phplib/util.php");

define('MIN_LIST_LENGTH', 10);
define('MAX_LIST_LENGTH', 2500);
define('DEFAULT_LIST_LENGTH', 100);
define('DEFAULT_SHOW_LIST', 0);

//define('RANDOM_WORDS_QUERY', 'select cuv %s from RandomWord where id in (%s)');
define('NO_WORDS', 'select count(*) from RandomWord');
//define('RANDOM_WORD', "select cuv from RandomWord limit $no_words[0],1");
define('SOURCE_PART_RANDOM_WORDS', ', surse');
$listLength = 5;
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
*/
//define('RANDOM_WORD', "select cuv from RandomWord limit $no_words[0],1");

$no_words = db_getArray(NO_WORDS);
echo $no_words[0];
$random_word = "select cuv from RandomWord limit $no_words[0],1";
//echo $random_word;
$query = db_getArrayOfRows($random_word);
echo $query;
//echo $query[0];$query = sprintf(RANDOM_WORDS_QUERY, $showSource?SOURCE_PART_RANDOM_WORDS:'', $listLength);
//$forms = db_getArrayOfRows($query);
//$cnt = count($forms);
$cnt=0;
if ($noSkin) {
    //smarty_assign('forms', $forms);
    smarty_displayWithoutSkin('common/randomWord.ihtml');
}
else {
    smarty_assign('forms', $query);
    smarty_assign('page_title', "O listă de {$cnt} de cuvinte alese la întâmplare.");
    smarty_displayCommonPageWithSkin('randomWord.ihtml');
}
?>
