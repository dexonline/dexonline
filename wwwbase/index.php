<?php
require_once("../phplib/util.php");

$skin = util_getRequestParameter('skin');

if ($skin) {
  if (session_isValidSkin($skin)) {
    session_setSkin($skin);
  } else {
    session_setFlash('Numele skinului este incorect.');
  }
  util_redirect('index.php');
}

smarty_assign('page_title', 'DEX online - Dicționar explicativ al limbii române');
smarty_assign('slick_selected', 'index');
smarty_displayPageWithSkin('index.ihtml');
?>
