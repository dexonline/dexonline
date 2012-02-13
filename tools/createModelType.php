<?php

require_once("../phplib/util.php");

define('MODEL_TYPE', 'SP');
define('MODEL_TYPE_DESCRIPTION', 'Substantiv propriu');
$INFLECTIONS = array(1 => 'Substantiv masculin, Nominativ-Acuzativ, singular, nearticulat',
                     2 => 'Substantiv masculin, Genitiv-Dativ, singular, nearticulat',
                     3 => 'Substantiv masculin, Nominativ-Acuzativ, plural, nearticulat',
                     4 => 'Substantiv masculin, Genitiv-Dativ, plural, nearticulat',
                     5 => 'Substantiv masculin, Nominativ-Acuzativ, singular, articulat',
                     6 => 'Substantiv masculin, Genitiv-Dativ, singular, articulat',
                     7 => 'Substantiv masculin, Nominativ-Acuzativ, plural, articulat',
                     8 => 'Substantiv masculin, Genitiv-Dativ, plural, articulat'
                     );

$existingMT = ModelType::get_by_code(MODEL_TYPE);

if ($existingMT) {
  die('Tipul de model ' . MODEL_TYPE . " există deja.\n");
}

$mt = Model::factory('ModelType')->create();
$mt->code = MODEL_TYPE;
$mt->description = MODEL_TYPE_DESCRIPTION;
$mt->canonical = MODEL_TYPE;
$mt->save();

foreach ($INFLECTIONS as $rank => $description) {
  $i = Model::factory('Inflection')->create();
  $i->description = $description;
  $i->modelType = MODEL_TYPE;
  $i->rank = $rank;
  $i->save();
}

$m = Model::factory('FlexModel')->create();
$m->modelType = MODEL_TYPE;
$m->number = '1';
$m->description = '';
$m->exponent = 'exponent';
$m->flag = 0;
$m->save();

?>
