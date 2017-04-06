<?php

/**
 * Label verb lexems with group / conjugation tags.
 **/

require_once __DIR__ . '/../phplib/util.php';

$POS_TAG_ID = 43; // ID of the 'part of speech' tag (parent of 'verb' tag)

if (count($argv) != 2) {
  die("Usage: {$argv[0]} <CSV filename>\n");
}

$f = fopen($argv[1], 'r') or die("Missing or corrupt CSV file.\n");

// remove old-style group tags
$tagValues = [
  'verb grupa I',
  'verb grupa a II-a',
  'verb grupa a III-a',
  'verb grupa a IV-a',
];
$tags = Model::factory('Tag')
      ->where_in('value', $tagValues)
      ->find_many();
foreach ($tags as $tag) {
  ObjectTag::delete_all_by_tagId($tag->id);
}

// load or create the new tags we'll need
$verbTag = getOrCreatetag('verb', $POS_TAG_ID);

$groupTags = [];
foreach (['I', 'II', 'III', 'IV'] as $g) {
  $tagValue = groupName($g);
  $groupTags[$g] = getOrCreatetag($tagValue, $verbTag->id);
}

$conjugationTags = [];
foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'nereg.'] as $c) {
  $tagValue = conjugationName($c);
  $conjugationTags[$c] = getOrCreatetag($tagValue, $verbTag->id);
}

// process the CSV file line by line
while (($line = fgetcsv($f, 1000)) !== false) {
  list($models, $dlrmModels, $conjugation, $group, $example) = $line;
  if ($models &&
      ($models != 'Dexonline') &&
      (strpos($conjugation, ',') === false)) {

    $parts = preg_split('/[ ,]+/', $models);
    foreach ($parts as $model) {

      print "[$model] [$group] [$conjugation]\n";

      // tag all the lexems having this verb model
      $lexems = Model::factory('Lexem')
              ->where_in('modelType', ['V', 'VT'])
              ->where('modelNumber', $model)
              ->order_by_asc('formNoAccent')
              ->find_many();
      foreach ($lexems as $l) {
        // collect the tags we want to add to this verb
        $tags = [ $verbTag, $groupTags[$group], $conjugationTags[$conjugation] ];

        print "  * {$l}";
        foreach ($tags as $t) {
          print " [{$t->value}]";
        }
        print "\n";

        // add the tags that don't already exist
        foreach ($tags as $t) {
          $ot = ObjectTag::get_by_objectType_objectId_tagId(ObjectTag::TYPE_LEXEM, $l->id, $t->id);
          if (!$ot) {
            $ot = Model::factory('ObjectTag')->create();
            $ot->objectType = ObjectTag::TYPE_LEXEM;
            $ot->objectId = $l->id;
            $ot->tagId = $t->id;
            $ot->save();
          }
        }
      }
    }
  }
}

/*************************************************************************/

function getOrCreatetag($value, $parentId) {
  $t = Tag::get_by_value_parentId($value, $parentId);
  if (!$t) {
    $t = Model::factory('Tag')->create();
    $t->value = $value;
    $t->parentId = $parentId;
    $t->displayOrder = 1 + Model::factory('Tag')->where('parentId', $parentId)->count();
    $t->save();
  }
  return $t;
}

function groupName($n) {
  if ($n == 'I') {
    return 'grupa I';
  } else {
    return "grupa a {$n}-a";
  }
}

function conjugationName($n) {
  if ($n == 'I') {
    return 'conjugarea I';
  } else if ($n == 'nereg.') {
    return 'conjugare neregulată';
  } else {
    return "conjugarea a {$n}-a";
  }
}
