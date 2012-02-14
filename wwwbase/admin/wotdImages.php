<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();
RecentLink::createOrUpdate('Word of the Day - Imagini');

smarty_assign('sectionTitle', 'Imagini pentru cuvântul zilei');
smarty_displayWithoutSkin('admin/wotdImages.ihtml');
?>
