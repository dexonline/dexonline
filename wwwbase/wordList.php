<?php
require_once("../phplib/util.php");

$letter = util_getRequestParameter('letter');

if (mb_strlen($letter) != '1') {
  exit;
}

$forms = db_getArray("select distinct formNoAccent from Lexem where formNoAccent like '{$letter}%' order by formNoAccent");

SmartyWrap::assign('forms', $forms);
SmartyWrap::assign('letter', $letter);
SmartyWrap::assign('page_title', "Cuvinte care încep cu " . mb_strtoupper($letter));
SmartyWrap::displayCommonPageWithSkin('wordList.ihtml');
?>
