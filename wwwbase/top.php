<?php
require_once("../phplib/util.php");

smarty_assign('data', TopEntry::getTopData(CRIT_CHARS, SORT_DESC));
smarty_assign('page_title', 'Topul voluntarilor');
smarty_displayCommonPageWithSkin('top.ihtml');
?>
