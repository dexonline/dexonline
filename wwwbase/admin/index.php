<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$models = FlexModel::loadByType('A');

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('canEditWotd', util_isModerator(PRIV_WOTD));
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
smarty_assign('modelTypes', ModelType::loadCanonical());
smarty_assign('models', $models);
smarty_assign('sectionTitle', 'Pagina moderatorului');
smarty_addCss('autocomplete');
smarty_addJs('jquery', 'autocomplete');
smarty_displayAdminPage('admin/index.ihtml');
?>
