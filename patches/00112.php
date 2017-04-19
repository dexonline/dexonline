<?php

$lexemIds = DB::getArray('select distinct lexemId from Meaning');

foreach($lexemIds as $lexemId) {
  $t = Meaning::loadTree($lexemId);
  renumber($t, '');
}

/**************************************************************************/

function renumber($t, $prefix) {
  if (empty($t)) {
    return;
  }
  if ($prefix) {
    $prefix .= '.';
  }
  foreach($t as $i => $tuple) {
    $m = $tuple['meaning'];
    $m->breadcrumb = $prefix . ($i + 1);
    $m->save();
    renumber($tuple['children'], $m->breadcrumb);
  }
}

?>
