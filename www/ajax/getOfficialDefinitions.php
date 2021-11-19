<?php

/**
 * Called at the end of a Hangman game.
 */

require_once '../../lib/Core.php';

$word = Request::get('word');

$definitions = Model::factory('Definition')
  ->table_alias('d')
  ->select('d.*')
  ->distinct()
  ->join('Source', ['s.id', '=', 'd.sourceId'], 's')
  ->join('EntryDefinition', ['ed.definitionId', '=', 'd.id'], 'ed')
  ->join('Entry', ['e.id', '=', 'ed.entryId'], 'e')
  ->join('EntryLexeme', ['el.entryId', '=', 'e.id'], 'el')
  ->join('Lexeme', ['l.id', '=', 'el.lexemeId'], 'l')
  ->where('l.formNoAccent', $word)
  ->where('el.main', true)
  ->where('e.adult', false)
  ->where('d.status', Definition::ST_ACTIVE)
  ->where('s.type', Source::TYPE_OFFICIAL)
  ->order_by_asc('s.sourceTypeId')
  ->order_by_asc('s.displayOrder')
  ->find_many();

$results = SearchResult::mapDefinitionArray($definitions);

$html = '';
foreach ($results as $row) {
  Smart::assign('row', $row);
  $html .= Smart::fetch('bits/definition.tpl');
}

$resp = ['html' => $html];

header('Content-Type: application/json');
echo json_encode($resp);
