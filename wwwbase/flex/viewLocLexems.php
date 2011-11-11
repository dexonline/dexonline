<?php
require_once("../../phplib/util.php"); 
util_hideEmptyRequestParameters();
util_assertModerator(PRIV_LOC);
util_assertNotMirror();

$sourceId = util_getRequestParameter('source');
$nick = util_getRequestParameter('nick');
$loc = util_getRequestParameter('loc');

$source = Source::get_by_id($sourceId);
switch ($loc) {
  case 0:
    $srcClause = " and not l.isLoc";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName} neincluse în LOC");
    break;
  case 1:
    $srcClause = " and l.isLoc";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName} incluse în LOC");
    break;
  case 2:
    $srcClause = "";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName}");
    break;
  default: exit;
}

$nickClause = '';
if ($nick) {
  $user = User::get_by_nick($nick);
  if ($user) {
    $nickClause = "and d.userId = {$user->id}";
  }
}

$query = "select l.* from Lexem l, LexemDefinitionMap ldm, Definition d where l.id = ldm.lexemId and ldm.definitionId = d.id and d.status = 0 and d.sourceId = $sourceId " .
  "$nickClause $srcClause order by l.formNoAccent";
$lexemDbResult = db_execute($query);

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexemDbResult', $lexemDbResult);
smarty_displayWithoutSkin('admin/lexemListDbResult.ihtml');

?>
