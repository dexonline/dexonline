<?php
require_once("../phplib/util.php");

$saveButton = util_getBoolean('saveButton');

if ($saveButton) {
  util_assertModerator(PRIV_ADMIN);
  $order = 1;
  $ids = util_getRequestParameter('ids');
  foreach ($ids as $id) {
    $src = Source::get_by_id($id);
    $src->displayOrder = $order++;
    $src->save();
  }
  Log::info('Reordered sources');
  FlashMessage::add('Am salvat ordinea.', 'success');
  util_redirect('surse');
}

if (util_isModerator(PRIV_VIEW_HIDDEN)) {
  $sources = Model::factory('Source')
           ->order_by_asc('displayOrder')
           ->find_many();
} else {
  $sources = Model::factory('Source')
           ->where_not_equal('isOfficial', SOURCE_TYPE_HIDDEN)
           ->order_by_asc('displayOrder')
           ->find_many();
}

SmartyWrap::assign('src', $sources);
SmartyWrap::addCss('jqueryui', 'admin');
SmartyWrap::addJs('jqueryui', 'jqTableDnd', 'tablesorter');
SmartyWrap::display('surse.tpl');

?>
