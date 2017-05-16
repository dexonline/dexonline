<?php

/**
 * Merge entries mapped to the exact same set of definitions.
 **/

require_once __DIR__ . '/../../phplib/Core.php';

Log::info('started');

$data = DB::execute('select entryId, group_concat(definitionId order by definitionId) as defIds ' .
                    'from EntryDefinition ' .
                    'group by entryId ' .
                    'having count(*) >= 2 ' .
                    'order by defIds, entryId ',
                    PDO::FETCH_ASSOC);

$prev = null;

foreach ($data as $row) {
  if ($prev && ($prev['defIds'] == $row['defIds'])) {
    $e1 = Entry::get_by_id($prev['entryId']);
    $e2 = Entry::get_by_id($row['entryId']);

    if ($e1->structuristId || $e2->structuristId) {
      Log::info('not merging [%s] into [%s] because there is a structuristId', $e2, $e1);
    } else {
      Log::info('merging [%s] into [%s]', $e2, $e1);

      // try to remove the () in the description
      if (preg_match('/([^(]+) \(([^)]+)\)$/', $e1->description, $matches)) {
        $desc = $matches[1];
        $parent = $matches[2];
        if (preg_match('/^pl\. /', $parent) ||
            preg_match('/^[123] /', $parent)) {
          $e1->description = $desc;
          $e1->save();
          Log::info('removed parenthesis [%s] for entry [%s]', $parent, $e1);
        } else {
          Log::info('cannot parse parenthesis [%s] of entry [%s]', $parent, $e1);
        }
      }

      $e2->mergeInto($e1->id);
    }
    // keep the same $prev
  } else {
    $prev = $row;
  }
}
