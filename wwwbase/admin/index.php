<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$models = FlexModel::loadByType('A');

SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign("allStatuses", util_getAllStatuses());
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('modelTypes', ModelType::loadCanonical());
SmartyWrap::assign('models', $models);
SmartyWrap::assign('structStatusNames', Lexem::$STRUCT_STATUS_NAMES);
SmartyWrap::assign('sectionTitle', 'Pagina moderatorului');
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'select2', 'select2Dev');
SmartyWrap::displayAdminPage('admin/index.ihtml');
?>
