<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Lexeme neasociate');

$lexems = Lexem::loadUnassociated();

smarty_assign('lexems', $lexems);
smarty_assign('sectionTitle', 'Lexeme neasociate');
smarty_assign('sectionCount', count($lexems));
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayAdminPage('admin/lexemList.ihtml');

?>
