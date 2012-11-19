<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme cu comentarii');
$lexems = Model::factory('Lexem')->where_not_equal('comment', '')->order_by_asc('formNoAccent')->find_many();

smarty_assign('sectionTitle', 'Lexeme cu comentarii');
smarty_assign('sectionCount', count($lexems));
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_displayAdminPage('flex/viewLexemsWithComments.ihtml');

?>
