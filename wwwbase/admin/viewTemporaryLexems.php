<?php
require_once('../../phplib/util.php');
ini_set('memory_limit', '512M');
User::require(User::PRIV_EDIT);
Util::assertNotMirror();

$sourceId = Request::get('source');

if ($sourceId) {
  $source = Source::get_by_id($sourceId);
  $lexems = Model::factory('Lexem')
          ->table_alias('l')
          ->select('l.*')
          ->distinct()
          ->join('EntryLexem', ['el.lexemId', '=', 'l.id'], 'el')
          ->join('EntryDefinition', ['ed.entryId', '=', 'el.entryId'], 'ed')
          ->join('Definition', 'd.id = ed.definitionId', 'd')
          ->where('d.status', Definition::ST_ACTIVE)
          ->where('d.sourceId', $sourceId)
          ->where('l.modelType', 'T')
          ->order_by_asc('formNoAccent')
          ->limit(1000)
          ->find_many();
} else {
  $lexems = Model::factory('Lexem')
          ->where('modelType', 'T')
          ->order_by_asc('formNoAccent')
          ->limit(1000)
          ->find_many();
}

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewTemporaryLexems.tpl');

?>
