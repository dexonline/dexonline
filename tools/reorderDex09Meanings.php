<?php

/**
 * Issue #752
 **/

require_once __DIR__ . '/../lib/Core.php';

const SOURCE_ID_98 = 1;
const SOURCE_ID_09 = 27;
const INFTY = 1000000000;

$trees = Model::factory('Tree')
  ->table_alias('t')
  ->select('t.*')
  ->distinct()
  ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
  ->join('Entry', ['te.entryId', '=', 'e.id'], 'e')
  ->where('e.structStatus', Entry::STRUCT_STATUS_IN_PROGRESS)
  ->order_by_asc('t.description')
  ->find_many();

foreach ($trees as $t) {
  $meanings = Model::factory('Meaning')
    ->where('treeId', $t->id)
    ->order_by_asc('displayOrder')
    ->find_many();

  // Check that the tree is flat.
  // Check that each meaning has only the sources DEX '98 or DEX '09.
  // Separate meanings from DEX '98 only.
  $usable = true;
  $dex98Meanings = [];
  $dex09Meanings = [];
  foreach ($meanings as $m) {
    $usable &= !$m->parentId;

    $sources = $m->getSources();
    $has98 = false;
    $has09 = false;
    $hasOther = false;
    foreach ($sources as $s) {
      if ($s->id == SOURCE_ID_98) {
        $has98 = true;
      } else if ($s->id == SOURCE_ID_09) {
        $has09 = true;
      } else {
        $hasOther = true;
      }
    }
    $usable &= ($has98 || $has09) && !$hasOther;

    if ($has98) {
      $dex98Meanings[] = $m;
    } else {
      $dex09Meanings[] = $m;
    }
  }

  // otherwise there is nothing to reorder
  $usable &= count($dex98Meanings) && count($dex09Meanings);

  if ($usable) {
    // for each DEX '98 meaning, store DEX '09 meanings mapping to it
    $map = [];

    foreach ($dex09Meanings as $new) {
      $minDist = INFTY;
      $minId = null;
      foreach ($dex98Meanings as $old) {
        $dist = DiffUtil::diffMeasure($old->internalRep, $new->internalRep);
        if ($dist < $minDist) {
          $minDist = $dist;
          $minId = $old->id;
        }
      }
      $map[$minId][] = $new;
    }

    // now do the renumbering
    Log::info("New order for tree {$t->description} is:");
    $displayOrder = 1;
    $bc = 1;
    foreach ($dex98Meanings as $m) {
      foreach ($map[$m->id] ?? [] as $new) {
        renumber($new, $displayOrder, $bc);
        Log::info("* (MOVED) {$new->displayOrder} {$new->internalRep}");
      }
      renumber($m, $displayOrder, $bc);
      Log::info("* {$m->displayOrder} {$m->internalRep}");
    }
  }
}

/*************************************************************************/

function renumber(&$meaning, &$displayOrder, &$bc) {
  $meaning = Meaning::get_by_id($meaning->id); // clear the sources field
  $meaning->displayOrder = $displayOrder++;
  if ($meaning->type == Meaning::TYPE_MEANING) {
    $meaning->breadcrumb = "{$bc}.";
    $bc++;
  }
  $meaning->save();
}
