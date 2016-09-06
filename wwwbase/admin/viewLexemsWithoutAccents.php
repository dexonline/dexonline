<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '512M');
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$lexems = Model::factory('Lexem')
  ->where('consistentAccent', 0)
  ->order_by_asc('formNoAccent')
  ->limit(1000)
  ->find_many();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewLexemsWithoutAccents.tpl');

?>
