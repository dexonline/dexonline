<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '512M');
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$lexems = Model::factory('Lexem')
  ->select('id')
  ->select('formNoAccent')
  ->select('description')
  ->where('consistentAccent', 0)
  ->order_by_asc('formNoAccent')
  ->limit(1000)
  ->find_many();

RecentLink::createOrUpdate('Lexeme fără accent');

SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::displayAdminPage('admin/viewLexemsWithoutAccents.tpl');

?>
