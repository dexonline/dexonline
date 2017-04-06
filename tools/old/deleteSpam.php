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

  Typo::delete_all_by_definitionId($def->id);
  EntryDefinition::dissociateDefinition($def->id);
  $def->delete();
}

print sprintf("Deleted %d definitions\n", count($defs));

?>
