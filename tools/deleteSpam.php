<?php
/**
 * This script deletes spammy definitions sent in bulk
 **/

require_once __DIR__ . '/../phplib/util.php';

$defs = Model::factory('Definition')
  ->where('userId', 0)
  ->where('status', Definition::ST_PENDING)
  ->where_like('internalRep', '%[url=%')
  ->find_many();

foreach ($defs as $def) {
  print sprintf("Deleting ID = %d, rep = %s\n", $def->id, StringUtil::shortenString($def->internalRep, 100));

  $defId = $def->id;
  $def->delete();
  db_execute("delete from Typo where definitionId = {$defId}");

  // TODO: This code replicates code in definitionEdit.php
  // If by deleting this definition, any associated lexems become unassociated, delete them
  $ldms = LexemDefinitionMap::get_all_by_definitionId($defId);
  db_execute("delete from LexemDefinitionMap where definitionId = {$defId}");

  foreach ($ldms as $ldm) {
    $l = Lexem::get_by_id($ldm->lexemId);
    $otherLdms = LexemDefinitionMap::get_all_by_lexemId($l->id);
    if (!$l->isLoc && !count($otherLdms)) {
      $l->delete();
    }
  }
}

print sprintf("Deleted %d definitions\n", count($defs));

?>
