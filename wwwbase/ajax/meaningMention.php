<?php
require_once("../../phplib/util.php");

$form = Request::get('form');
$qualifier = Request::get('qualifier');

$data = Model::factory('InflectedForm')
      ->table_alias('if')
      ->select('t.description')
      ->select('m.id')
      ->select('m.type')
      ->select('m.breadcrumb')
      ->select('m.htmlRep')
      ->distinct()
      ->join('Lexem', ['if.lexemId', '=', 'l.id'], 'l')
      ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
      ->join('Entry', ['el.entryId', '=', 'e.id'], 'e')
      ->join('TreeEntry', ['e.id', '=', 'te.entryId'], 'te')
      ->join('Tree', ['te.treeId', '=', 't.id'], 't')
      ->join('Meaning', ['t.id', '=', 'm.treeId'], 'm')
      ->where('if.formNoAccent', $form)
      ->where_any_is([['m.breadcrumb' => "{$qualifier}%"],
                      ['m.id' => "{$qualifier}%"]],
                     'like')
      ->order_by_asc('t.description')
      ->order_by_asc('m.type')
      ->order_by_asc('m.displayOrder')
      ->find_many();

$results = [];
foreach ($data as $r) {
  $rep = $r->htmlRep;
  if (!$rep && $r->type == Meaning::TYPE_MEANING) {
    // empty meanings usually have synonyms
    $relations = Relation::loadByMeaningId($r->id);
    $synonyms = $relations[Relation::TYPE_SYNONYM];
    $rep = sprintf('<i>sinonime:</i> %s',
                   implode(', ', util_objectProperty($synonyms, 'description')));
  }
  else if (!$rep && $r->type == Meaning::TYPE_ETYMOLOGY) {
    // empty etyomologies usually have tags
    $tags = Model::factory('Tag')
          ->table_alias('t')
          ->join('ObjectTag', ['t.id', '=', 'ot.tagId'], 'ot')
          ->where('ot.objectType', ObjectTag::TYPE_MEANING)
          ->where('ot.objectId', $r->id)
          ->order_by_asc('ot.id')
          ->find_many();
    $rep = sprintf('<i>etichete:</i> %s',
                   implode(', ', util_objectProperty($tags, 'value')));
  }
  $results[] = [
    'description' => $r->description,
    'meaning' => $rep,
    'meaningId' => $r->id,
    'breadcrumb' => $r->breadcrumb,
  ];
}

header('Content-Type: application/json');
print json_encode($results);

?>
