<?php

require_once __DIR__ . '/../../phplib/util.php';

$DOR_SOURCE_ID = 38;

$defs = Model::factory('Definition')
      ->select('id')
      ->where('sourceId', $DOR_SOURCE_ID)
      ->order_by_asc('lexicon')
      ->find_many();

foreach ($defs as $defId) {
  $d = Definition::get_by_id($defId->id);
  $newLexicon = AdminStringUtil::extractLexicon($d);
  if ($newLexicon != $d->lexicon) {
    Log::info("changed lexicon for {$d->id} from [{$d->lexicon}] to [{$newLexicon}]");
    $d->lexicon = $newLexicon;
    $d->save();
  }
}
