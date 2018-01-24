<?php
require_once('../../phplib/Core.php');

$form = Request::get('form');
$field = (strpos($form, "'") === false) ? 'formNoAccent' : 'form';

$l = Model::factory('Lexem')
   ->table_alias('l')
   ->select('l.*')
   ->join('InflectedForm', ['l.id', '=', 'f.lexemeId'], 'f')
   ->join('Inflection', ['f.inflectionId', '=', 'i.id'], 'i')
   ->where("f.{$field}", $form)
   ->order_by_expr('i.modelType in ("V", "T", "I")')  // avoid some model types
   ->order_by_asc('i.rank')                // prefer more "basic" inflections
   ->find_one();

if ($l) {
  $resp = [
    'id' => $l->id,
    'text' => (string)$l,
    'capitalized' => Str::isUppercase($form),
  ];
} else {
  $resp = null;
}

header('Content-Type: application/json');
print json_encode($resp);
