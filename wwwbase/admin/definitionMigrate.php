<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$id = util_getRequestIntParameter('id');

if (!$id) {
  $def = Model::factory('Definition')->where('status', ST_ACTIVE)->where('sourceId', 1)->order_by_asc('lexicon')->find_one();
  util_redirect("?id={$def->id}");
}

$def = Definition::get_by_id($id);
$source = Source::get_by_id($def->sourceId);
$lexems = Model::factory('Lexem')->select('Lexem.*')->join('LexemDefinitionMap', 'Lexem.id = lexemId', 'ldm')->where('ldm.definitionId', $id)->find_many();

SmartyWrap::assign('def', $def);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('sectionTitle', "Structurare definiție: {$def->id}");
SmartyWrap::addCss('jqueryui', 'jqgrid', 'structure');
SmartyWrap::addJs('jquery', 'jqueryui', 'jqgrid', 'structure');
SmartyWrap::displayAdminPage('admin/definitionMigrate.ihtml');

?>
