<?php

/**
 * Convert comments to footnotes.
 **/

$comments = Model::factory('Comment')
          ->where('status', Definition::ST_ACTIVE)
          ->order_by_asc('definitionId')
          ->find_many();

printf("Converting %d comments...\n", count($comments));

foreach ($comments as $i => $c) {
  if ($i && ($i % 500 == 0)) {
    print "$i comments converted.\n";
  }
  $d = Definition::get_by_id($c->definitionId);
  $d->internalRep .= sprintf('{{%s/%s}}', $c->contents, $c->userId);

  $footnotes = $d->process();
  $d->save();
  foreach ($footnotes as $f) {
    $f->definitionId = $d->id;
    $f->save();
  }
  $c->delete();
}
