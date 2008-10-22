<?php
require_once("../phplib/util.php");

smarty_assign('page_title', 'DEX online - Unelte');
smarty_assign('show_search_box', 0);
smarty_assign('slick_selected', 'tools');
smarty_displayCommonPageWithSkin('tools.ihtml');
?>
