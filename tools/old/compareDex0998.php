<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/util.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';
ini_set('memory_limit', '1024M');

define('SOURCE_ID', 27);
define('OLDER_SOURCE_IDS', [1, 2, 5, 3, 4]);
define('BATCH_SIZE', 10000);
define('START_AT', '');
define('EDIT_URL', 'https://dexonline.ro/admin/definitionEdit.php?definitionId=');

Log::info('started');

$offset = 0;
$numDifferent = 0;

do {
  $defs = Model::factory('Definition')
        ->where('sourceId', SOURCE_ID)
        ->where('status', Definition::ST_ACTIVE)
        ->where_gte('lexicon', START_AT)
        ->order_by_asc('lexicon')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $diffSize = null;
    $old = loadSimilar($d, $diffSize);

    if ($old && ($old->internalRep != $d->internalRep)) {
      $numDifferent++;
      Log::error('Different: %s %s%d %d', $d->lexicon, EDIT_URL, $d->id, $old->id);
    }
  }

  $offset += BATCH_SIZE;
  Log::info("Processed $offset definitions, $numDifferent different.");
} while (count($defs));

Log::info('ended');


/*************************************************************************/

// Adapted from Definition::loadSimilar to include several editions
function loadSimilar($def, &$diffSize = null) {
  $result = null;

  $eds = EntryDefinition::get_all_by_definitionId($def->id);
  $entryIds = util_objectProperty($eds, 'entryId');

  // First see if there is a similar source
  if (count($entryIds)) {
    // Load all definitions mapped to any of $entryIds
    $candidates = Model::factory('Definition')
                ->table_alias('d')
                ->select('d.*')
                ->distinct()
                ->join('EntryDefinition', ['ed.definitionId', '=', 'd.id'], 'ed')
                ->where_not_equal('d.status', Definition::ST_DELETED)
                ->where_in('d.sourceId', OLDER_SOURCE_IDS)
                ->where_in('ed.entryId', $entryIds)
                ->find_many();

    // Find the definition with the minimum diff from the original
    $diffSize = 0;
    foreach ($candidates as $d) {
      $size = LDiff::diffMeasure($def->internalRep, $d->internalRep);
      if (!$result || ($size < $diffSize)) {
        $result = $d;
        $diffSize = $size;
      }
    }
  }

  return $result;
}

