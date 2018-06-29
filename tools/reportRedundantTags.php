<?php

/**
 * Report objects that have two labels A and B where A is an ancestor of B.
 **/

require_once __DIR__ . '/../phplib/Core.php';

$tags = Model::factory('Tag')->find_many();

foreach ($tags as $t) {
  $ancestors = $t->getAncestors();
  array_pop($ancestors); // do not include $t in its ancestor list
  foreach ($ancestors as $a) {
    $query = sprintf(
      'select a.* ' .
      'from ObjectTag a ' .
      'join ObjectTag b ' .
      'on a.objectId = b.objectId ' .
      'and a.objectType = b.objectType ' .
      'where a.tagId = %d ' .
      'and b.tagId = %d ',
      $t->id,
      $a->id
    );
    $objects = Model::factory('ObjectTag')
      ->raw_query($query)
      ->find_many();
    foreach ($objects as $o) {
      // printf("Tags [%s] and [%s] both apply to id %d, type %d\n",
      //        $a->value, $t->value, $o->objectId, $o->objectType);
      printf("{$a->value} + {$t->value}\n", $a->value, $t->value);
    }
  }
}
