<?php
require_once("../phplib/util.php");

smarty_assign('page_title', 'DEX online - Informații');
smarty_assign('show_search_box', 0);
smarty_assign('slick_sel', 'faq');
smarty_displayCommonPageWithSkin('faq.ihtml');
?>
