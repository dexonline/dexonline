<?php
require_once("../phplib/util.php");

$letter = util_getRequestParameter('letter');
$forms = db_getArray(db_execute("select distinct formNoAccent from Lexem where formUtf8General like '{$letter}%' order by formNoAccent"));

smarty_assign('forms', $forms);
smarty_assign('letter', $letter);
smarty_displayCommonPageWithSkin('wordList.ihtml');
?>
