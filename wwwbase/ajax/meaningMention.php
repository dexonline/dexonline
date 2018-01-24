<?php
require_once("../../phplib/Core.php");

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
      ->join('Lexeme', ['if.lexemeId', '=', 'l.id'], 'l')
      ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
      ->join('Entry', ['el.entryId', '=', 'e.id'], 'e')
      ->join('TreeEntry', ['e.id', '=', 'te.entryId'], 'te')
      ->join('Tree', ['te.treeId', '=', 't.id'], 't')
      ->join('Meaning', ['t.id', '=', 'm.treeId'], 'm')
      ->where('if.formNoAccent', $form)
      ->where('m.type', Meaning::TYPE_MEANING)
      ->where_any_is([['m.breadcrumb' => "{$qualifier}%"],
                      ['m.id' => "{$qualifier}%"]],
                     'like')
      ->order_by_asc('t.description')
      ->order_by_asc('m.displayOrder')
      ->find_many();

$results = [];
foreach ($data as $r) {
  $rep = $r->htmlRep;
  if (!$rep) {
    // empty meanings usually have synonyms
    $relations = Relation::loadByMeaningId($r->id);
    $synonyms = $relations[Relation::TYPE_SYNONYM];
    $rep = sprintf('<i>sinonime:</i> %s',
                   implode(', ', Util::objectProperty($synonyms, 'description')));
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
