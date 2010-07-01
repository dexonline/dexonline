<?php
require_once("../phplib/util.php");
require_once("../phplib/ads/adsModule.php");

// Display a custom ad 50% of the times
if (rand(0, 99) < 50) {
  AdsModule::runAllModules(null, null);
}

smarty_assign('page_title', 'Dicționar explicativ al limbii române');
smarty_assign('onHomePage', '1');
smarty_assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz'));
smarty_displayPageWithSkin('index.ihtml');
?>
