<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$models = FlexModel::loadByType('A');

$counters = array(
  'ambiguousAbbrevs' => Model::factory('Definition')->where_not_equal('status', ST_DELETED)->where('abbrevReview', ABBREV_AMBIGUOUS)->count(),
  'definitionsWithTypos' => Model::factory('Typo')->select('definitionId')->distinct()->count(),
  'lexemsWithoutAccents' => Model::factory('Lexem')->where('consistentAccent', 0)->count(),
  'ocrAvailDefs' => Model::factory('OCR')->where('status', 'raw')->where_raw(sprintf('(editorId is null or editorId = %d)', session_getUserId()))->count(),
  'ocrDefs' => Model::factory('OCR')->where('status', 'raw')->count(),
  'pendingDefinitions' => Model::factory('Definition')->where('status', ST_PENDING)->count(),
  'temporaryLexems' => Model::factory('Lexem')->where('modelType', 'T')->count(),
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
