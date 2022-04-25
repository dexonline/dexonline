<?php

/* Change some regular meanings to expressions. */

const PATCH_DEBUG = false;
const EXPRESSION_TAG_ID = 10; // [expresie] (11.693 occurrences)
const TAG_IDS = [
  EXPRESSION_TAG_ID,
  26,  // [(în) sintagmă] (1.496 occurrences)
  35,  // [compus] (221 occurrences)
  39,  // [locuțiune prepozițională] (171 occurrences)
  114, // [locuțiune] (78 occurrences)
  115, // [locuțiune adjectivală] (751 occurrences)
  116, // [locuțiune adverbială] (1.908 occurrences)
  117, // [locuțiune conjuncțională] (74 occurrences)
  119, // [locuțiune verbală] (239 occurrences)
  198, // [locuțiune substantivală] (4 occurrences)
];

function loadTrees() {
  return Model::factory('Tree')
    ->table_alias('t')
    ->select('t.*')
    ->distinct()
    ->join('Meaning', ['m.treeId', '=', 't.id'], 'm')
    ->join('ObjectTag', ['ot.objectId', '=', 'm.id'], 'ot')
    ->where('ot.objectType', ObjectTag::TYPE_MEANING)
    ->where_in('ot.tagId', TAG_IDS)
    ->where('m.type', Meaning::TYPE_MEANING)
    ->order_by_asc('description')
    ->find_many();
}

/**
 * Returns a map of $meaningId => true for meanings tagged with the above tags
 */
function loadExpressionIds() {
  $ids = Model::factory('Meaning')
    ->table_alias('m')
    ->select('m.id')
    ->distinct()
    ->join('ObjectTag', ['ot.objectId', '=', 'm.id'], 'ot')
    ->where('ot.objectType', ObjectTag::TYPE_MEANING)
    ->where_in('ot.tagId', TAG_IDS)
    ->where('m.type', Meaning::TYPE_MEANING)
    ->find_array();

  $ids = array_column($ids, 'id');
  return array_fill_keys($ids, true);
}

/**
 * Reorders children of $parentId. Returns the reordered meanings.
 */
function reorderHelper($meanings, $parentId) {
  $n = count($meanings);

  if (!$n) {
    return [];
  }

  // split $meanings into [ $directChild, [ subtree of $directChild ] ]...
  $children = [];

  $prev = 0;
  for ($i = 1; $i <= $n; $i++) {
    if (($i == $n) || ($meanings[$i]->parentId == $parentId)) {
      $children[] = [
        $meanings[$prev],
        array_slice($meanings, $prev + 1, $i - $prev - 1),
      ];
      $prev = $i;
    }
  }

  // Recurse first. After this, expressions within each subtree will be
  // well-placed.
  for ($i = 0; $i < count($children); $i++) {
    $children[$i][1] = reorderHelper($children[$i][1], $children[$i][0]->id);
  }

  // Now check if any work needs to be done. Find the last meaning.
  $last = count($children) - 1;
  while (($last >= 0) && ($children[$last][0]->type != Meaning::TYPE_MEANING)) {
    $last--;
  }

  if ($last >= 0) {
    $meanings = [];
    // collect non-expressions up to and including $last
    for ($i = 0; $i <= $last; $i++) {
      if ($children[$i][0]->type != Meaning::TYPE_EXPRESSION) {
        array_push($meanings, $children[$i][0], ...$children[$i][1]);
      }
    }
    // collect expressions up to and including $last
    for ($i = 0; $i <= $last; $i++) {
      if ($children[$i][0]->type == Meaning::TYPE_EXPRESSION) {
        array_push($meanings, $children[$i][0], ...$children[$i][1]);
      }
    }
    // collect remaining children
    for ($i = $last + 1; $i < count($children); $i++) {
      array_push($meanings, $children[$i][0], ...$children[$i][1]);
    }
  }

  return $meanings;
}

/**
 * For every parent meaning's children, migrate expressions that are followed
 * by meanings to immediately after the last meaning.

 * Example: M1 E1 E2 other1 M2 E3 E4 other2 M3 other3 E5 E6 ==>
 *          M1 other1 M2 other2 M3 E1 E2 E3 E4 other3 E5 E6
 *
 * This is rather complex because there can be subtrees rooted at every child.
 */
function reorder($meanings) {
  return reorderHelper($meanings, 0);

}

/**
 * Ensure we have an identical set of meaning IDs after reordering.
 */
function compare($oldIds, $meanings) {
  $newIds = Util::objectProperty($meanings, 'id');
  empty(array_diff($oldIds, $newIds)) or die("ERROR: Meaning set has changed!\n");
  empty(array_diff($newIds, $oldIds)) or die("ERROR: Meaning set has changed!\n");
}

function main() {
  $trees = loadTrees();
  $expressionIds = loadExpressionIds();

  foreach ($trees as $i => $t) {
    printf("Migrating tree %d/%d [%s]\n", $i + 1, count($trees), $t->description);

    $meanings = Model::factory('Meaning')
      ->where('treeId', $t->id)
      ->order_by_asc('displayOrder')
      ->find_many();
    $origIds = Util::objectProperty($meanings, 'id');
    $meaningMap = Util::mapById($meanings);

    foreach ($meanings as $m) {
       // Make a change if either the meaning is tagged or it has an expression ancestor.
      if (isset($expressionIds[$m->id]) ||
          (($m->type == Meaning::TYPE_MEANING) &&
           $m->parentId &&
           ($meaningMap[$m->parentId]->type == Meaning::TYPE_EXPRESSION))) {

        printf("    Changing meaning %s to expression\n", $m->breadcrumb);
        $m->type = Meaning::TYPE_EXPRESSION;
        if (!PATCH_DEBUG) {
          ObjectTag::dissociate(ObjectTag::TYPE_MEANING, $m->id, EXPRESSION_TAG_ID);
        }

      }
    }

    $meanings = reorder($meanings);
    Meaning::renumber($meanings);

    if (PATCH_DEBUG) {
      foreach ($meanings as $m) {
        printf("    %6d %6d %2d %3d %-10s %s\n",
               $m->id, $m->parentId, $m->type, $m->displayOrder, $m->breadcrumb,
               mb_substr($m->internalRep, 0, 80));
      }
    }

    compare($origIds, $meanings);

    if (!PATCH_DEBUG) {
      foreach ($meanings as $m) {
        $m->save();
      }
    }

    if (PATCH_DEBUG)  {
      exit; // stop after every tree
    }
  }

  if (PATCH_DEBUG) {
    exit; // don't let patch number increment
  }
}

main();
