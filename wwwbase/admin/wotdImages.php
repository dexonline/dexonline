<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();
RecentLink::createOrUpdate('Word of the Day - Imagini');

smarty_assign('sectionTitle', 'Imagini pentru cuvântul zilei');
smarty_addCss('elfinder', 'jquery_smoothness');
smarty_addJs('jquery', 'jqueryui', 'elfinder');
smarty_displayAdminPage('admin/wotdImages.ihtml');
?>
