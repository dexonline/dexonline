<?php

require_once '../../lib/Core.php';

$id = Request::get('id');
$guessed = Request::get('guessed');

$md = MillData::get_by_id($id);
if ($md) {
  $md->shown++;
  $md->guessed += $guessed;
  $md->ratio = $md->guessed / $md->shown;
  $md->save();
}
