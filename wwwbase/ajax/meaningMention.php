<?php
require_once '../../phplib/Core.php';

$form = Request::get('form');
$qualifier = Request::get('qualifier');

$data = Model::factory('Meaning')
      ->table_alias('m')
      ->select('m.*')
      ->select('t.description')
      ->distinct()
      ->join('Tree', ['t.id', '=', 'm.treeId'], 't')
      ->join('TreeEntry', ['te.treeId', '=', 't.id'], 'te')
      ->join('Entry', ['e.id', '=', 'te.entryId'], 'e')
      ->join('EntryLexeme', ['el.entryId', '=', 'e.id'], 'el')
      ->join('Lexeme', ['l.id', '=', 'el.lexemeId'], 'l')
      ->join('InflectedForm', ['if.lexemeId', '=', 'l.id'], 'if')
      ->where('if.formNoAccent', $form)
      ->where('m.type', Meaning::TYPE_MEANING)
      ->where_any_is([['m.breadcrumb' => "{$qualifier}%"],
                      ['m.id' => "{$qualifier}%"]],
                     'like')
      ->order_by_asc('t.descriptionSort')
      ->order_by_asc('m.displayOrder')
      ->find_many();

$results = [];
foreach ($data as $r) {
  $rep = HtmlConverter::convert($r);
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
