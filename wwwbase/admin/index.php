<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$models = Model::loadByType('A');

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign("allModeratorSources", db_find(new Source(), 'canModerate order by displayOrder'));
smarty_assign('modelTypes', ModelType::loadCanonical());
smarty_assign('models', $models);
smarty_displayWithoutSkin('admin/index.ihtml');

?>
