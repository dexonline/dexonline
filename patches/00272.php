<?php

$meanings = Model::factory('Meaning')
  ->where_like('internalRep', '%[%')
  ->order_by_asc('id')
  ->find_many();

foreach ($meanings as $m) {
  $s = preg_replace_callback(
    '/(?<!\[)\[([0-9]+)(\*{0,2})\]/',
    'starToggle',
    $m->internalRep);
  if ($s != $m->internalRep) {
    printf("Before: ...%s...\nAfter:  ...%s...\n", $m->internalRep, $s);
    $m->internalRep = $s;
    $m->save();
  }
}

/*************************************************************************/

function starToggle($match) {
  $id = $match[1];
  $numStars = strlen($match[2]);

  switch ($numStars) {
    case 0: $stars = '*'; break;
    case 1: $stars = ''; break;
    case 2: $stars = '**'; break;
  }

  return "[{$id}{$stars}]";
}
