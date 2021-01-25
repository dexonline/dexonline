<?php
/**
 * Output two lists of words associated with some common dictionaries, once with diacritical
 * marks and once without them.
 **/

require_once __DIR__ . '/../lib/Core.php';

Log::notice('started');

$startTimestamp = time();

// select visible trees associated with structured entries
Log::info('collecting root meanings');
$trees = Model::factory('Tree')
  ->table_alias('t')
  ->select('t.*')
  ->distinct()
  ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
  ->join('Entry', ['te.entryId', '=', 'e.id'], 'e')
  ->where('t.status', Tree::ST_VISIBLE)
  ->where('e.structStatus', Entry::STRUCT_STATUS_DONE)
  ->order_by_asc('t.description')
  ->find_many();
Log::info('Processing %d trees', count($trees));

$count = 0;
foreach ($trees as $t) {
  $desc = $t->getShortDescription();
  $posMask = MillData::getPosMask($t->id, $desc);

  if ($posMask && (mb_strlen($desc) >= 3)) { // skip short words
    // get root meanings
    $meanings = Model::factory('Meaning')
      ->where('treeId', $t->id)
      ->where('parentId', 0)
      ->where('type', Meaning::TYPE_MEANING)
      ->where_not_equal('internalRep', '')
      ->find_many();

    // create MillData entries for each meaning
    foreach ($meanings as $m) {
      $md = MillData::get_by_meaningId($m->id);
      if (!$md) {
        $md = Model::factory('MillData')->create();
        $md->meaningId = $m->id;
      }
      $md->word = $desc;
      $md->posMask = $posMask;
      $md->internalRep = $m->internalRep;
      $md->save();
    }
  };

  if (++$count % 1000 == 0) {
    Log::info("{$count} trees processed");
  }
}

// Delete receords not created or updated during this run
Log::info('cleaning up old records');
DB::execute("delete from MillData where modDate < {$startTimestamp}");

Log::notice('finished');
