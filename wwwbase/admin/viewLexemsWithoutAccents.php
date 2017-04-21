<?php
require_once("../../phplib/Core.php"); 
ini_set('memory_limit', '512M');
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$lexems = Model::factory('Lexem')
  ->where('consistentAccent', 0)
  ->order_by_asc('formNoAccent')
  ->limit(1000)
  ->find_many();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewLexemsWithoutAccents.tpl');

?>
