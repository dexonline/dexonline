<?php
require_once("../phplib/util.php");

smarty_assign('page_title', 'DEX online - Dicționar explicativ al limbii române');
smarty_assign('slick_selected', 'index');
smarty_displayPageWithSkin('index.ihtml');
?>
