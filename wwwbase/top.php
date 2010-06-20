<?php
require_once("../phplib/util.php");

smarty_assign('manualData', TopEntry::getTopData(CRIT_CHARS, SORT_DESC, true));
smarty_assign('bulkData', TopEntry::getTopData(CRIT_CHARS, SORT_DESC, false));
smarty_assign('page_title', 'Topul voluntarilor');
smarty_displayCommonPageWithSkin('top.ihtml');
?>
