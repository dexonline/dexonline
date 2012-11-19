<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme ambigue');
$lexems = Model::factory('Lexem')->raw_query("select * from Lexem where description = '' group by form having count(*) > 1", null)->find_many();

SmartyWrap::assign('sectionTitle', 'Lexeme ambigue (cu nume și descriere identice)');
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::displayAdminPage('admin/lexemList.ihtml');

?>
