<?php

require_once("../../phplib/Core.php");

$defId = Request::get('defId');
$guessed = Request::get('guessed');

$def = DefinitionSimple::get_by_id($defId);
if ($def) {
  $def->millShown++;
  $def->millGuessed += $guessed;
  $def->save();
}
