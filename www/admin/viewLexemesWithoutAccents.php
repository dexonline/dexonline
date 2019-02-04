<?php
require_once '../../lib/Core.php'; 
ini_set('memory_limit', '512M');
User::mustHave(User::PRIV_EDIT);

$lexemes = Model::factory('Lexeme')
  ->where('consistentAccent', 0)
  ->order_by_asc('formNoAccent')
  ->limit(1000)
  ->find_many();

SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewLexemesWithoutAccents.tpl');
