<?
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$defId = util_getRequestParameter('id');
$def = Definition::get("id = {$defId}");
if ($def && $def->id) {
  $def->status = ST_DELETED;
  $def->save();
  db_execute("delete from Typo where definitionId = {$def->id}");

  // TODO: This code replicates code in definitionEdit.php
  // If by deleting this definition, any associated lexems become unassociated, delete them
  $ldms = db_find(new LexemDefinitionMap(), "definitionId = {$def->id}");
  db_execute("delete from LexemDefinitionMap where definitionId = {$def->id}");

  foreach ($ldms as $ldm) {
    $l = Lexem::get("id = {$ldm->lexemId}");
    $otherLdms = db_find(new LexemDefinitionMap(), "lexemId = {$l->id}");
    if (!$l->isLoc && !count($otherLdms)) {
      $l->delete();
    }
  }
}

?>
