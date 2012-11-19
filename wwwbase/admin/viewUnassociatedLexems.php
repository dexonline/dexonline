<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Lexeme neasociate');

$lexems = Lexem::loadUnassociated();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('sectionTitle', 'Lexeme neasociate');
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/lexemList.ihtml');

?>
