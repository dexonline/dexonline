<?php
require_once("../../phplib/util.php");

$form = Request::get('form');
$qualifier = Request::get('qualifier');

$data = Model::factory('InflectedForm')
      ->table_alias('if')
      ->select('t.description')
      ->select('m.id')
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
      ->order_by_asc('m.breadcrumb')
      ->find_many();

$results = [];
foreach ($data as $r) {
  $results[] = [
    'description' => $r->description,
    'meaning' => $r->htmlRep,
    'meaningId' => $r->id,
    'breadcrumb' => $r->breadcrumb,
  ];
}

print json_encode($results);

?>
