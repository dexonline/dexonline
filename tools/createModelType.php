<?php

require_once __DIR__ . '/../lib/Core.php';

const MODEL_TYPE = 'SP';
const MODEL_TYPE_DESCRIPTION = 'Substantiv propriu';
const INFLECTIONS = [
  1 => 'Substantiv propriu, Nominativ-Acuzativ, singular, nearticulat',
  2 => 'Substantiv propriu, Genitiv-Dativ, singular, nearticulat',
  3 => 'Substantiv propriu, Nominativ-Acuzativ, plural, nearticulat',
  4 => 'Substantiv propriu, Genitiv-Dativ, plural, nearticulat',
  5 => 'Substantiv propriu, Nominativ-Acuzativ, singular, articulat',
  6 => 'Substantiv propriu, Genitiv-Dativ, singular, articulat',
  7 => 'Substantiv propriu, Nominativ-Acuzativ, plural, articulat',
  8 => 'Substantiv propriu, Genitiv-Dativ, plural, articulat',
];

/** Also, don't forget to edit templates/paradigm/paradigm.tpl accordingly. **/

$existingMT = ModelType::get_by_code(MODEL_TYPE);

if ($existingMT) {
  die('Tipul de model ' . MODEL_TYPE . " există deja.\n");
}

$mt = Model::factory('ModelType')->create();
$mt->code = MODEL_TYPE;
$mt->description = MODEL_TYPE_DESCRIPTION;
$mt->canonical = MODEL_TYPE;
$mt->save();

foreach (INFLECTIONS as $rank => $description) {
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
$m->save();
