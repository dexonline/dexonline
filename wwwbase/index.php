<?php
require_once("../phplib/util.php");

// TODO: Change the skin in the session and redirect to /, don't use a GET arg.
$skin = util_getRequestParameter('skin');

if ($skin) {
  if (session_isValidSkin($skin)) {
    session_setSkin($skin);
  } else {
    echo 'Numele skinului este incorect.';
    return;
  }
}

smarty_assign('page_title',
	      'DEX online - Dicţionar explicativ al limbii române');
smarty_assign('slick_selected', 'index');
smarty_displayPageWithSkin('index.ihtml');
?>
