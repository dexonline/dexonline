<?php

User::mustHave(User::PRIV_EDIT);

$saveButton = Request::has('saveButton');
$sourceId = Request::get('sourceId');

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

$def = Model::factory('Definition');

if ($sourceId) {
  $def = $def->where('sourceId', $sourceId);
}

$def = $def
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

$sources = Model::factory('Source')
  ->table_alias('s')
  ->select('s.*')
  ->select_expr('count(*)', 'numAmbiguous')
  ->join('Definition', ['s.id', '=', 'd.sourceId'], 'd')
  ->where('d.hasAmbiguousAbbreviations', true)
  ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
  ->group_by('s.id')
  ->order_by_asc('s.displayOrder')
  ->find_many();

Smart::assign([
  'def' => $def,
  'sourceId' => $sourceId,
  'sources' => $sources,
]);
Smart::addResources('admin');
Smart::display('report/randomAbbrevReview.tpl');

/**
 * Sort matches from last to first
 */
function positionCmp($a, $b) {
  return $b['position'] - $a['position'];
}
