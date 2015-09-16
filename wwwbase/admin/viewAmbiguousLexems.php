<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme ambigue');
$lexems = Lexem::loadAmbiguous();

SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::displayAdminPage('admin/viewAmbiguousLexems.tpl');

?>
