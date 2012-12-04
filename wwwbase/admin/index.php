<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$models = FlexModel::loadByType('A');

SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('canEditWotd', util_isModerator(PRIV_WOTD));
SmartyWrap::assign("allStatuses", util_getAllStatuses());
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('modelTypes', ModelType::loadCanonical());
SmartyWrap::assign('models', $models);
SmartyWrap::assign('sectionTitle', 'Pagina moderatorului');
SmartyWrap::addCss('jqueryui');
SmartyWrap::addJs('jquery', 'jqueryui');
SmartyWrap::displayAdminPage('admin/index.ihtml');
?>
