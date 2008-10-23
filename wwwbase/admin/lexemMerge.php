<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

$id1 = util_getRequestParameter('id1');
$id2 = util_getRequestParameter('id2');

$l1 = Lexem::load($id1);
$l2 = Lexem::load($id2);

$defs = Definition::loadByLexemId($l1->id);
foreach ($defs as $def) {
  LexemDefinitionMap::associate($l2->id, $def->id);
}

$l1->delete();
util_redirect("lexemEdit.php?lexemId={$l2->id}");

?>
