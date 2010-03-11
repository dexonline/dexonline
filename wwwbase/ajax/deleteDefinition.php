<?
require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();

$defId = util_getRequestParameter('id');
$def = Definition::get("id = {$defId}");
if ($def && $def->id) {
  $def->status = ST_DELETED;
  $def->save();
  db_execute("delete from Typo where definitionId = {$def->id}");

  // TODO: This code replicates code in definitionEdit.php
  // If by deleting this definition, any associated lexems become unassociated, delete them
  $ldms = LexemDefinitionMap::loadByDefinitionId($def->id);
  LexemDefinitionMap::deleteByDefinitionId($def->id);

  foreach ($ldms as $ldm) {
    $l = Lexem::load($ldm->lexemId);
    $otherLdms = LexemDefinitionMap::loadByLexemId($l->id);
    if (!$l->isLoc && !count($otherLdms)) {
      $l->delete();
    }
  }
}

?>
