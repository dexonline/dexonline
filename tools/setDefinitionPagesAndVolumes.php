<?php

/**
 * Calculates the volume and page fields for definitions that have volume =
 * page = 0 if the corresponding source has a page index.
 *
 * This is somewhat slow (~2,000 defs/second), but it reuses existing code
 * (Definition::setVolumeAndPage).
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('BATCH_SIZE', 10000);

$total = 0;

do {
  $data = Model::factory('Definition')
        ->table_alias('d')
        ->select('d.id')
        ->join('Source', ['d.sourceId', '=', 's.id'], 's')
        ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
        ->where('s.hasPageImages', true)
        ->where('d.volume', 0)
        ->where('d.page', 0)
        ->limit(BATCH_SIZE)
        ->find_array();

  foreach ($data as $row) {
    $id = $row['id'];
    $d = Definition::get_by_id($id);
    $d->setVolumeAndPage();
    $d->save();
    // Log::info('Set volume %d and page %d for definition %d [%s] in source %d',
    //           $d->volume, $d->page, $d->id, $d->lexicon, $d->sourceId);
  }
  $total += count($data);

  Log::info('Altered %d definitions', $total);

} while (count($data));
