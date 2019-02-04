<?php

/**
 * Report objects that have two labels A and B where A is an ancestor of B.
 **/

require_once __DIR__ . '/../lib/Core.php';

$tags = Model::factory('Tag')->find_many();

// read command line arguments
$opts = getopt('', ['fix', 'restrict::']);
$fix = isset($opts['fix']);
$restrictValue = $opts['restrict'] ?? null;

$restrictAncestor = null;
if ($restrictValue) {
  $restrictAncestor = Tag::get_by_value($restrictValue);
  if (!$restrictAncestor) {
    die("Unknown tag value {$restrictValue}\n");
  }
}

foreach ($tags as $t) {
  $ancestors = $t->getAncestors();
  array_pop($ancestors); // do not include $t in its ancestor list
  foreach ($ancestors as $a) {
    if (!$restrictAncestor || ($a->id == $restrictAncestor->id)) {
      $query = sprintf(
        'select a.* ' .
        'from ObjectTag a ' .
        'join ObjectTag b ' .
        'on a.objectId = b.objectId ' .
        'and a.objectType = b.objectType ' .
        'where a.tagId = %d ' .
        'and b.tagId = %d ',
        $a->id,
        $t->id
      );
      $objects = Model::factory('ObjectTag')
        ->raw_query($query)
        ->find_many();
      foreach ($objects as $o) {
        switch ($o->objectType) {
          case ObjectTag::TYPE_LEXEME:
            $url = "https://dexonline.ro/admin/lexemeEdit.php?lexemeId={$o->objectId}";
            break;
          case ObjectTag::TYPE_MEANING:
            $m = Meaning::get_by_id($o->objectId);
            $url = "https://dexonline.ro/editTree.php?id={$m->treeId}";
            break;
          default:
            die("Not sure how to fix object of type {$o->objectType}\n");
        }
        printf("{$url} {$a->value} + {$t->value}\n");
        if ($fix) {
          $o->delete();
        }
      }
    }
  }
}
