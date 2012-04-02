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

$sources = util_isModerator(PRIV_VIEW_HIDDEN) ? 
Model::factory('Source')->order_by_asc('displayOrder')->find_many():
Model::factory('Source')->where_not_equal('isOfficial',SOURCE_TYPE_HIDDEN)->order_by_asc('displayOrder')->find_many();


smarty_assign('sources', $sources);
smarty_assign('page_title', 'Surse');
smarty_displayCommonPageWithSkin('surse.ihtml');

?>
