<?php

require_once __DIR__ . '/../phplib/util.php';

// Select unassociateed definitions
$defs = Model::factory('Definition')->where_raw("status != 2 and id not in (select definitionId from LexemDefinitionMap)")->find_many();

foreach ($defs as $def) {
  // Load duplicates
  $md5rep = md5($def->internalRep);
  $dups = Model::factory('Definition')->where_raw("id != {$def->id} and md5(internalRep) = '{$md5rep}'")->find_many();
  if (count($dups)) {
    print ("Def {$def->id} {$def->lexicon} source {$def->sourceId} has dup {$dups[0]->id} {$dups[0]->lexicon} -- DELETING\n");
    $def->delete();
  } else {
    $slashRep = addslashes($def->internalRep);
    $dups = Model::factory('Definition')->where_raw("id != {$def->id} and lexicon = '{$def->lexicon}' and soundex(internalRep) = soundex('{$slashRep}')")->find_many();
    if (count($dups)) {
      print ("Def {$def->id} {$def->lexicon} source {$def->sourceId} has near dup {$dups[0]->id} {$dups[0]->lexicon} -- DELETING\n");
      $def->delete();
    }
  }
}


?>
