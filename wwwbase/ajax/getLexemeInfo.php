<?php
require_once("../../phplib/Core.php");

$id = Request::get('id');

$l = Lexeme::get_by_id($id);
$mt = ModelType::get_by_code($l->modelType);
$m = FlexModel::get_by_modelType_number($mt->canonical, $l->modelNumber);

// load the lexeme's tags and keep those referring to parts of speech
$posTagName = Config::get('tags.partOfSpeech');
$posTag = Tag::get_by_value($posTagName);

$posTags = [];
if ($posTag) {
  $tags = ObjectTag::getTags($l->id, ObjectTag::TYPE_LEXEME);
  foreach ($tags as $t) {
    if ($t->isDescendantOf($posTag)) {
      $posTags[] = [
        'id' => $t->id,
        'text' => $t->value,
      ];
    }
  }
}


$results = [
  'modelType' => $l->modelType,
  'modelNumber' => $l->modelNumber,
  'restriction' => $l->restriction,
  'exponent' => $m->exponent,
  'posTags' => $posTags,
];

header('Content-Type: application/json');
print json_encode($results);
