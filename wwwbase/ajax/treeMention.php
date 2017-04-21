<?php
require_once("../../phplib/Core.php");

User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);

$form = Request::get('form');

$data = Model::factory('InflectedForm')
      ->table_alias('if')
      ->select('t.id')
      ->select('t.description', 'treeDescription')
      ->select('e.description', 'entryDescription')
      ->distinct()
      ->join('Lexem', ['if.lexemId', '=', 'l.id'], 'l')
      ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
      ->join('Entry', ['el.entryId', '=', 'e.id'], 'e')
      ->join('TreeEntry', ['e.id', '=', 'te.entryId'], 'te')
      ->join('Tree', ['te.treeId', '=', 't.id'], 't')
      ->where('if.formNoAccent', $form)
      ->where('t.status', Tree::ST_VISIBLE)
      ->order_by_asc('treeDescription')
      ->order_by_asc('entryDescription')
      ->find_many();

$results = [];
foreach ($data as $r) {
  $results[] = [
    'treeId' => $r->id,
    'treeDescription' => $r->treeDescription,
    'entryDescription' => $r->entryDescription,
  ];
}

print json_encode($results);

?>
