<?php
require_once("../phplib/util.php");

$letter = util_getRequestParameter('letter');

if (mb_strlen($letter) != '1') {
  exit;
}

$forms = db_getArray("select distinct formNoAccent from Lexem where formNoAccent like '{$letter}%' order by formNoAccent");

smarty_assign('forms', $forms);
smarty_assign('letter', $letter);
smarty_assign('page_title', "Cuvinte care încep cu " . mb_strtoupper($letter));
smarty_displayCommonPageWithSkin('wordList.ihtml');
?>
