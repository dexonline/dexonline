<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '512M');
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$lexems = Model::factory('Lexem')->where_raw("form not rlike '\'' and not noAccent")->find_many();

RecentLink::createOrUpdate('Lexeme fără accent');

smarty_assign('sectionTitle', 'Lexeme fără accent');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_assign('sectionCount', count($lexems));
smarty_displayAdminPage('admin/lexemList.ihtml');

?>
