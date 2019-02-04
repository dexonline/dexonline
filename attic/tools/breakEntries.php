<?php
/**
 * Load entries having multiple trees and try to split them into single-tree entries.
 **/

require_once __DIR__ . '/../lib/Core.php';

Log::notice('started');

$entries = Model::factory('Entry')
         ->select('Entry.*')
         ->join('TreeEntry', ['Entry.id', '=', 'TreeEntry.entryId'])
         ->where_in('structStatus', [Entry::STRUCT_STATUS_UNDER_REVIEW, Entry::STRUCT_STATUS_DONE])
         ->group_by('Entry.id')
         ->having_raw('count(*) > 1')
         ->order_by_asc('description')
         ->find_many();

foreach ($entries as $e) {
  $lexems = $e->getLexems();
  $trees = $e->getTrees();

  if (count($trees) < 2) {
    Log::warning('skipping [%s] with %d trees', $e, count($trees));
    continue;
  }

  if (count($lexems) > 1) {
    printf("[%s] with %d trees and %d lexemes\n", $e, count($trees), count($lexems));
  }
  continue;

  // keep track of how many lexemes each tree receives
  $count = [];
  $desc = [];
  foreach ($trees as $t) {
    $count[$t->id] = 0;

    // get the part before the (, if any, lowercased and trimmed of spaces and dashes
    $part = explode('(', $t->description)[0];
    $desc[$t->id] = mb_strtolower(trim($part, ' -'));
  }

  $orphans = false;

  foreach ($lexems as $l) {
    $found = false;

    foreach ($trees as $t) {
      if (mb_strtolower($l->formNoAccent) == $desc[$t->id]) {
        $count[$t->id]++;
        $found = true;
      }
    }

    if (!$found) {
      $l->orphan = true;
      $orphans = true;
    }

    foreach ($trees as $t) {
      if (!$count[$t->id]) {
        $t->orphan = true;
        $orphans = true;
      }
    }
  }

  if ($orphans) {
    print "lexeme:";
    foreach ($lexems as $l) {
      printf(" [%s%s]", $l->orphan ? '*' : '', $l);
    }
    print "\narbori:";
    foreach ($trees as $t) {
      printf(" [%s%s]", $t->orphan ? '*' : '', $t->description);
    }
    print "\n----\n";
  }
}

Log::notice('finished');
