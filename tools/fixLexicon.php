<?php

require_once __DIR__ . '/../phplib/Core.php';

define('MIN_SOURCE_ID', 1);
define('MAX_SOURCE_ID', 100);
define('DRY_RUN', true);

ini_set('memory_limit','512M');

$sources = Model::factory('Source')
         ->where_gte('id', MIN_SOURCE_ID)
         ->where_lte('id', MAX_SOURCE_ID)
         ->find_many();

DB::setBuffering(false);

foreach ($sources as $s) {
  print "*********** Source {$s->shortName} (ID = {$s->id})\n";

  $defs = Model::factory('Definition')
        ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
        ->where('sourceId', $s->id)
        ->order_by_asc('lexicon')
        ->find_many();

  foreach ($defs as $d) {
    $newLexicon = AdminStringUtil::extractLexiconNew($d);
    if ($newLexicon != $d->lexicon) {
      printf(
        "https://dexonline.ro/admin/definitionEdit.php?definitionId=%s [%s] -> [%s] rep = [%s]\n",
        $d->id,
        AdminStringUtil::padRight($d->lexicon, 20),
        AdminStringUtil::padRight($newLexicon, 20),
        mb_substr($d->internalRep, 0, 50)
      );
      if (!DRY_RUN) {
        $d->lexicon = $newLexicon;
        $d->save();
      }
    }
  }
}
