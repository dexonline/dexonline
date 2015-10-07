<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$defId = util_getRequestParameter('id');
$def = Definition::get_by_id($defId);
if ($def && $def->id) {
  $def->status = Definition::ST_DELETED;
  $def->save();
  db_execute("delete from Typo where definitionId = {$def->id}");

  // TODO: This code replicates code in definitionEdit.php
  // If by deleting this definition, any associated lexems become unassociated, delete them
  $ldms = LexemDefinitionMap::get_all_by_definitionId($def->id);
  db_execute("delete from LexemDefinitionMap where definitionId = {$def->id}");

  foreach ($ldms as $ldm) {
    $l = Lexem::get_by_id($ldm->lexemId);
    $otherLdms = LexemDefinitionMap::get_all_by_lexemId($l->id);
    if (!$l->isLoc() && !count($otherLdms)) {
      $l->delete();
    }
  }
}

?>
