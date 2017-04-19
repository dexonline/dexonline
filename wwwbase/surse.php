<?php
require_once('../phplib/Core.php');

$saveButton = Request::has('saveButton');

if ($saveButton) {
  User::require(User::PRIV_ADMIN);
  $order = 1;
  $ids = Request::get('ids');
  foreach ($ids as $id) {
    $src = Source::get_by_id($id);
    $src->displayOrder = $order++;
    $src->save();
  }
  Log::info('Reordered sources');
  FlashMessage::add('Am salvat ordinea.', 'success');
  Util::redirect('surse');
}

if (User::can(User::PRIV_VIEW_HIDDEN)) {
  $sources = Model::factory('Source')
           ->order_by_asc('displayOrder')
           ->find_many();
} else {
  $sources = Model::factory('Source')
           ->where_not_equal('type', Source::TYPE_HIDDEN)
           ->order_by_asc('displayOrder')
           ->find_many();
}

SmartyWrap::assign('src', $sources);
SmartyWrap::assign('editable', User::can(User::PRIV_ADMIN));
SmartyWrap::addCss('admin');
SmartyWrap::addJs('jqTableDnd', 'tablesorter');
SmartyWrap::display('surse.tpl');

?>
