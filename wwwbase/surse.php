<?php
require_once("../phplib/util.php");

$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  util_assertModerator(PRIV_ADMIN);
  $order = 1;
  $ids = util_getRequestParameter("ids");
  foreach ($ids as $id) {
    $src = Source::get_by_id($id);
    $src->displayOrder = $order++;
    $src->save();
  }
  FlashMessage::add('Ordinea a fost salvată.', 'info');
  util_redirect('surse');
}

smarty_assign('sources', Model::factory('Source')->order_by_asc('displayOrder')->find_many());
smarty_assign('page_title', 'Surse');
smarty_displayCommonPageWithSkin('surse.ihtml');

?>
