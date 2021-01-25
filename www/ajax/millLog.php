<?php

require_once '../../lib/Core.php';

$meaningId = Request::get('meaningId');
$guessed = Request::get('guessed');

$m = Meaning::get_by_id($meaningId);
if ($m) {
  $m->millShown++;
  $m->millGuessed += $guessed;
  $m->millRatio = $m->millGuessed / $m->millShown;
  $m->save();
}
