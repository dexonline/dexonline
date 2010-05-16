<?

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Lexeme neasociate');

$lexems = Lexem::loadUnassociated();

smarty_assign('lexems', $lexems);
smarty_assign('sectionTitle', 'Lexeme neasociate');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
