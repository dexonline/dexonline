<?php

/**
 * Rename some poorly named trees after the DEX '98 structure.
 **/

require_once __DIR__ . '/../lib/util.php';

define('SOURCE_ID', 1);
define('MY_USER_ID', 1);
define('START_AT', '');

Log::info('started');

$query = sprintf('select t.id ' .
                 'from Meaning m ' .
                 'join MeaningSource ms on m.id = ms.meaningId ' .
                 'join Tree t on m.treeId = t.id ' .
                 'where ms.sourceId = %d ' .
                 'and t.status = 0 ' .
                 'and t.description >= binary "%s" ' .
                 'group by m.treeId ' .
                 'having min(m.userId) = %d ' .
                 'and max(m.userId) = %d ' .
                 'order by t.description ',
                 SOURCE_ID, START_AT, MY_USER_ID, MY_USER_ID);
$treeIds = db_getArray($query);

foreach ($treeIds as $i => $treeId) {
  $t = Tree::get_by_id($treeId);

  $entries = $t->getEntries();

  if (count($entries) > 1) {
    $entryIds = util_objectProperty($entries, 'id');

    // find definitions from DEX '98 associated with all these entries
    $defs = Model::factory('Definition')
          ->table_alias('d')
          ->select('d.*')
          ->distinct()
          ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
          ->where('d.sourceId', SOURCE_ID)
          ->where('d.status', Definition::ST_ACTIVE)
          ->where_in('ed.entryId', $entryIds)
          ->group_by('d.id')
          ->having_raw(sprintf('count(*) = %s', count($entryIds)))
          ->find_many();

    $lexicons = array_unique(util_objectProperty($defs, 'lexicon'));
    if (count($lexicons) != 1) {
      Log::info('Multiple lexicons for tree [%s]', $t->description);
    } else if ($lexicons[0] != $t->description) {

      $descriptions = [];
      foreach ($entries as $e) {
        // drop the bracket, if any
        $descriptions[] = preg_replace('/ \([^)]+\)$/', '', $e->description);
      }

      if (in_array($lexicons[0], $descriptions)) {
        Log::info('Renaming tree [%s] to [%s]', $t->description, $lexicons[0]);
        $t->description = $lexicons[0];
        $t->save();
      } else {
        Log::info('No matching entry for tree [%s], lexicon is [%s]', $t->description, $lexicons[0]);
      }

    }
  }

  if ($i % 100000 == 0) {
    Log::info("Processed $i trees.");
  }
}

Log::info('ended');
