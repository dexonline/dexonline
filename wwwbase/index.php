<?php
require_once("../phplib/util.php");

smarty_assign('page_title', 'Dicționar explicativ al limbii române');
smarty_assign('onHomePage', '1');
smarty_assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz'));
smarty_displayPageWithSkin('index.ihtml');
?>
