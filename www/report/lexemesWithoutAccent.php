<?php
require_once '../../lib/Core.php';
ini_set('memory_limit', '512M');
User::mustHave(User::PRIV_EDIT);

$lexemes = Model::factory('Lexeme')
  ->where('consistentAccent', 0)
  ->order_by_asc('formNoAccent')
  ->limit(1000)
  ->find_many();

Smart::assign('lexemes', $lexemes);
Smart::addResources('admin');
Smart::display('report/lexemesWithoutAccent.tpl');
