<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme cu comentarii');
$lexems = Model::factory('Lexem')->where_not_null('comment')->order_by_asc('formNoAccent')->find_many();

SmartyWrap::assign('sectionTitle', 'Lexeme cu comentarii');
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::displayAdminPage('flex/viewLexemsWithComments.ihtml');

?>
