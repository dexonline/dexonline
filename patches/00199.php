<?php

// Factor out etymologies, comments and examples into separate meanings.

define('ETYMOLOGY_TAG_ID', 1);
define('EXAMPLE_TAG_ID', 3);

// select trees that have meanings
$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->join('Meaning', ['t.id', '=', 'm.treeId'], 'm')
       ->group_by('t.id')
       ->order_by_asc('t.description')
       ->find_many();

foreach ($trees as $t) {
  $meanings = Model::factory('Meaning')
            ->where('treeId', $t->id)
            ->order_by_asc('displayOrder')
            ->find_many();
  $newMeanings = []; // newly created meanings, mapped by parentId, not saved until the end

  foreach ($meanings as $m) {
    $ots = ObjectTag::getMeaningTags($m->id);

    // Case 1: The meaning has the [etymology] tag. In theory, one of the rep and etymology
    // fields should be empty (log an error if not). Set the type to TYPE_ETYMOLOGY, move
    // the contents to the rep field and remove the tag.
    if (hasTag($ots, ETYMOLOGY_TAG_ID)) {
      if ($m->internalRep && $m->internalEtymology) {
        Log::error(
          'Tree %s meaning %s needs your attention (has etymology label, meaning and etymology).',
          $t->description, $m->internalRep);
      }
      $m->type = Meaning::TYPE_ETYMOLOGY;
      $m->internalRep .= $m->internalEtymology;
      $m->htmlRep .= $m->htmlEtymology;
      $m->internalEtymology = '';
      $m->htmlEtymology = '';
      $m->save();
      ObjectTag::delete_all_by_objectId_objectType_tagId(
        $m->id, ObjectTag::TYPE_MEANING, ETYMOLOGY_TAG_ID);
      Log::info("Removed [etymology] from {$t->description} {$m->breadcrumb}");
    }

    // Case 2: The meaning has the [example] tag. In theory, the etymology field should be empty
    // (log an error if not). Set the type to TYPE_EXAMPLE and remove the tag.
    if (hasTag($ots, EXAMPLE_TAG_ID)) {
      if ($m->internalEtymology) {
        Log::error(
          'Tree %s meaning %s needs your attention (has example label and etymology).',
          $t->description, $m->internalRep);
      }
      $m->type = Meaning::TYPE_EXAMPLE;
      $m->save();
      ObjectTag::delete_all_by_objectId_objectType_tagId(
        $m->id, ObjectTag::TYPE_MEANING, EXAMPLE_TAG_ID);
      Log::info("Removed [example] from {$t->description} {$m->breadcrumb}");
    }

    // Case 3: The meaning has an etymology. Create a new meaning for it.
    if ($m->internalEtymology) {
      $c = Model::factory('Meaning')->create();
      $c->parentId = $m->id;
      $c->type = Meaning::TYPE_ETYMOLOGY;
      $c->userId = $m->userId;
      $c->treeId = $m->treeId;
      $c->internalRep = $m->internalEtymology;
      $c->htmlRep = $m->htmlEtymology;
      $m->internalEtymology = '';
      $m->htmlEtymology = '';
      $newMeanings[$m->id][] = $c;
      Log::info("Severed etymology from {$t->description} {$m->internalRep}");      
    }
 
    // Case 4: The meaning has a comment. Create a new meaning for it.
    if ($m->internalComment) {
      $c = Model::factory('Meaning')->create();
      $c->parentId = $m->id;
      $c->type = Meaning::TYPE_COMMENT;
      $c->userId = $m->userId;
      $c->treeId = $m->treeId;
      $c->internalRep = $m->internalComment;
      $c->htmlRep = $m->htmlComment;
      $m->internalComment = '';
      $m->htmlComment = '';
      $newMeanings[$m->id][] = $c;
      Log::info("Severed comment from {$t->description} {$m->internalRep}");      
    }
  }

  if (!empty($newMeanings)) {
    insertMeanings($meanings, $newMeanings);
    Log::info("Renumbered {$t->description}");
  }
}

/*************************************************************************/

function hasTag($objectTags, $tagId) {
  foreach ($objectTags as $ot) {
    if ($ot->tagId == $tagId) {
      return true;
    }
  }
  return false;
}

function insertMeanings($meanings, $newMeanings) {
  $merged = [];
  foreach ($meanings as $m) {
    $merged[] = $m;
    if (isset($newMeanings[$m->id])) {
      foreach ($newMeanings[$m->id] as $child) {
        $merged[] = $child;
      }
    }
  }

  Meaning::renumber($merged);

  foreach ($merged as $m) {
    $m->save();
  }
}
