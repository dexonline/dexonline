<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme fără accent');

smarty_assign('sectionTitle', 'Lexeme fără accent');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', Model::factory('Lexem')->where_raw("form not rlike '\'' and not noAccent")->find_many());
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
