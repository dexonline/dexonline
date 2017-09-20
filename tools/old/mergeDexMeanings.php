<?php

/**
 * Merge duplicate meanings from DEX '98 and DEX '09.
 **/

require_once __DIR__ . '/../phplib/Core.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';
ini_set('memory_limit', '1024M');

Log::info('started');

$treeIds = DB::getArray('select distinct t.id ' .
                        'from Meaning m1 ' .
                        'join Meaning m2 ' .
                        'on m1.treeId = m2.treeId ' .
                        'and m1.internalRep = m2.internalRep ' .
                        'and m1.type = m2.type ' .
                        'and m1.id != m2.id ' .
                        'join Tree t ' .
                        'on m1.treeId = t.id ' .
                        'join TreeEntry te ' .
                        'on t.id = te.treeId ' .
                        'join Entry e ' .
                        'on te.entryId = e.id ' .
                        'where !e.structuristId ' .
                        'and e.structStatus in (1, 2) ' .
                        'order by t.id');

foreach ($treeIds as $treeId) {
  $t = Tree::get_by_id($treeId);
  $meanings = Model::factory('Meaning')
            ->where('treeId', $t->id)
            ->order_by_asc('displayOrder')
            ->find_many();
  $newMeanings = [];

  // copy $meanings to $newMeanings while removing duplicates
  foreach ($meanings as $m) {
    $i = 0;
    while (($i < count($newMeanings)) &&
           (($m->internalRep != $newMeanings[$i]->internalRep) ||
            ($m->type != $newMeanings[$i]->type))) {
      $i++;
    }

    if (($i == count($newMeanings)) ||
        (($m->internalRep == '') && ($m->type == Meaning::TYPE_MEANING))) {
      $newMeanings[] = $m; // no duplicate found; don't touch empty meanings
    } else {
      $other = $newMeanings[$i];
      Log::info('Tree [%s] [https://dexonline.ro/editTree.php?id=%s]', $t->description, $t->id);
      Log::info('Meaning [%s]', $m->internalRep);

      // there should be no descendants, mentions or relations, but paranoia's good
      if (Meaning::get_by_parentId($m->id)) {
        die("has children\n");
      }
      if (Mention::get_by_meaningId($m->id) ||
          Mention::get_by_objectId_objectType($m->id, Mention::TYPE_MEANING)) {
        die("has mentions\n");
      }
      if (Relation::get_by_meaningId($m->id)) {
        die("has relations\n");
      }

      // sources
      $mss = MeaningSource::get_all_by_meaningId($m->id);
      foreach ($mss as $ms) {
        if (!MeaningSource::get_by_meaningId_sourceId($other->id, $ms->sourceId)) {
          Log::info("  copying sourceId {$ms->sourceId}");
          MeaningSource::associate($other->id, $ms->sourceId);
        }
      }

      // tags
      $ots = ObjectTag::getMeaningTags($m->id);
      foreach ($ots as $ot) {
        if (!ObjectTag::get_by_objectId_objectType_tagId($other->id, ObjectTag::TYPE_MEANING,
                                                         $ot->tagId)) {
          Log::info("  copying tagId {$ot->tagId}");
          ObjectTag::associate(ObjectTag::TYPE_MEANING, $other->id, $ot->tagId);
        }
      }

      $m->delete();
    }
  }

  // renumber the meanings if needed
  if (count($newMeanings) != count($meanings)) {
    Meaning::renumber($newMeanings);
    foreach ($newMeanings as $nm) {
      $nm->save();
    }
  }
}
