<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$models = FlexModel::loadByType('A');

$counters = array(
  'ambiguousAbbrevs' => Definition::countAmbiguousAbbrevs(),
  'definitionsWithTypos' => Model::factory('Typo')->select('definitionId')->distinct()->count(),
  'lexemsWithComments' => Model::factory('Lexem')->where_not_null('comment')->count(),
  'lexemsWithoutAccents' => Model::factory('Lexem')->where('consistentAccent', 0)->count(),
  'ocrAvailDefs' => OCR::countAvailable(session_getUserId()),
  'ocrDefs'=> Model::factory('OCR')->where('status', 'raw')->count(),
  'pendingDefinitions' => Model::factory('Definition')->where('status', ST_PENDING)->count(),
  'temporaryLexems' => Model::factory('Lexem')->where('modelType', 'T')->count(),

  // this takes about 300 ms
  'unassociatedDefinitions' => Definition::countUnassociated(),

  // this takes about 500 ms (even though the query is very similar to the one for unassociatedDefinitions)
  'unassociatedLexems' => Lexem::countUnassociated(),

  'unreviewedImages' => Model::factory('Visual')->where('revised', 0)->count(),
);

SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign("allStatuses", util_getAllStatuses());
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('modelTypes', ModelType::loadCanonical());
SmartyWrap::assign('models', $models);
SmartyWrap::assign('structStatusNames', Lexem::$STRUCT_STATUS_NAMES);
SmartyWrap::assign('counters', $counters);
SmartyWrap::assign('sectionTitle', 'Pagina moderatorului');
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'select2', 'select2Dev');
SmartyWrap::displayAdminPage('admin/index.ihtml');
?>
