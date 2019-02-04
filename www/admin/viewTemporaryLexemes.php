<?php
require_once '../../lib/Core.php';
ini_set('memory_limit', '512M');
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('source');

if ($sourceId) {
  $source = Source::get_by_id($sourceId);
  $lexemes = Model::factory('Lexeme')
          ->table_alias('l')
          ->select('l.*')
          ->distinct()
          ->join('EntryLexeme', ['el.lexemeId', '=', 'l.id'], 'el')
          ->join('EntryDefinition', ['ed.entryId', '=', 'el.entryId'], 'ed')
          ->join('Definition', 'd.id = ed.definitionId', 'd')
          ->where('d.status', Definition::ST_ACTIVE)
          ->where('d.sourceId', $sourceId)
          ->where('l.modelType', 'T')
          ->order_by_asc('formNoAccent')
          ->limit(1000)
          ->find_many();
} else {
  $lexemes = Model::factory('Lexeme')
          ->where('modelType', 'T')
          ->order_by_asc('formNoAccent')
          ->limit(1000)
          ->find_many();
}

Smart::assign('lexemes', $lexemes);
Smart::addCss('admin');
Smart::display('admin/viewTemporaryLexemes.tpl');
