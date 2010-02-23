<?php
require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();

$models = Model::loadByType('A');

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign("allModeratorSources", Source::loadAllModeratorSources());
smarty_assign('modelTypes', ModelType::loadCanonical());
smarty_assign('models', $models);
smarty_displayWithoutSkin('admin/index.ihtml');

?>
