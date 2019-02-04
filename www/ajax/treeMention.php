<?php
require_once '../../lib/Core.php';

User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);

$form = Request::get('form');

$data = Model::factory('InflectedForm')
      ->table_alias('if')
      ->select('t.id')
      ->select('t.description', 'treeDescription')
      ->select('e.description', 'entryDescription')
      ->distinct()
      ->join('Lexeme', ['if.lexemeId', '=', 'l.id'], 'l')
      ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
      ->join('Entry', ['el.entryId', '=', 'e.id'], 'e')
      ->join('TreeEntry', ['e.id', '=', 'te.entryId'], 'te')
      ->join('Tree', ['te.treeId', '=', 't.id'], 't')
      ->where('if.formNoAccent', $form)
      ->where('t.status', Tree::ST_VISIBLE)
      ->order_by_asc('t.descriptionSort')
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
