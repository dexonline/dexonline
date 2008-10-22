<?php
require_once("../phplib/util.php");

smarty_assign('page_title', 'DEX online - Licenţă');
smarty_assign('show_search_box', 0);
smarty_assign('slick_selected', 'faq');
smarty_displayCommonPageWithSkin('license.ihtml');
?>
