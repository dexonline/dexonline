<?php

/**
 * Remove "(nume propriu)" from etymologies and apply the [nume propriu] tag.
 **/

require_once __DIR__ . '/../lib/Core.php';

$tag = Tag::get_by_value('nume propriu')
  or die("Tag [nume propriu] not found.\n");

Log::info("Found tag $tag (ID {$tag->id})");

$meanings = Model::factory('Meaning')
  ->where('type', Meaning::TYPE_ETYMOLOGY)
  ->where_like('internalRep', '%(nume propriu)%')
  ->find_many();

Log::info('Processing %d meanings', count($meanings));

foreach ($meanings as $m) {
  Log::info('Processing meaning ID %d, tree ID %d: [%s]',
            $m->id, $m->treeId, $m->internalRep);

  ObjectTag::associate(ObjectTag::TYPE_MEANING, $m->id, $tag->id);

  $m->internalRep = str_replace('(nume propriu)', '', $m->internalRep);
  $m->internalRep = preg_replace("/[ \t]+/", ' ', $m->internalRep);
  $m->save();
}
