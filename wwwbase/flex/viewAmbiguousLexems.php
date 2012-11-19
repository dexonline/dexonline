<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme ambigue');
$lexems = Model::factory('Lexem')->raw_query("select * from Lexem where description = '' group by form having count(*) > 1", null)->find_many();

smarty_assign('sectionTitle', 'Lexeme ambigue (cu nume și descriere identice)');
smarty_assign('sectionCount', count($lexems));
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_displayAdminPage('admin/lexemList.ihtml');

?>
