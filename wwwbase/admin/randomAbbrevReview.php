<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$saveButton = Request::has('saveButton');

if ($saveButton) {
  $defId = Request::get('definitionId');
  $actions = Request::getJson('actions', []);

  $def = Definition::get_by_id($defId);

  // Collect the positions of ambiguous abbreviations
  list($def->internalRep, $matches)
    = Abbrev::markAbbreviations($def->internalRep, $def->sourceId);
  usort($matches, 'positionCmp');

  $s = $def->internalRep;
  foreach ($matches as $i => $m) {
    $action = $actions[count($actions) - 1 - $i];
    $replacement = ($action == 1) ? '#' : '##';
    $s = substr_replace($s, $replacement, $m['position'] + $m['length'], 0);
    $s = substr_replace($s, $replacement, $m['position'], 0);
  }
  $def->internalRep = $s;
  $def->process();
  $def->save();
}

$def = Model::factory('Definition')
  ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
  ->where('hasAmbiguousAbbreviations', true)
  ->order_by_expr('rand()')
  ->find_one();

if ($def) {
  // Collect the positions of ambiguous abbreviations
  list ($def->internalRep, $matches)
    = Abbrev::markAbbreviations($def->internalRep, $def->sourceId);
  usort($matches, 'positionCmp');

  // Inject our marker around each ambiguity and htmlize the definition
  foreach ($matches as $m) {
    $def->internalRep = substr_replace($def->internalRep, '#}', $m['position'] + $m['length'], 0);
    $def->internalRep = substr_replace($def->internalRep, '{#', $m['position'], 0);
  }
}

SmartyWrap::assign('def', $def);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/randomAbbrevReview.tpl');

/**
 * Sort matches from last to first
 */
function positionCmp($a, $b) {
  return $b['position'] - $a['position'];
}
