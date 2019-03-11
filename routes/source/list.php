<?php

$saveButton = Request::has('saveButton');

if ($saveButton) {
  User::mustHave(User::PRIV_ADMIN);
  $order = 1;
  $ids = Request::get('ids');
  foreach ($ids as $id) {
    $src = Source::get_by_id($id);
    $src->displayOrder = $order++;
    $src->save();
  }
  Log::info('Reordered sources');
  FlashMessage::add('Am salvat ordinea.', 'success');
  Util::redirectToSelf();
}

if (User::can(User::PRIV_VIEW_HIDDEN)) {
  $sources = Source::getAll();
} else {
  $sources = Model::factory('Source')
    ->where('hidden', false)
    ->order_by_asc('displayOrder')
    ->find_many();
}

Smart::assign('src', $sources);
Smart::assign('editable', User::can(User::PRIV_ADMIN));
Smart::addResources('admin', 'jqTableDnd', 'tablesorter');
Smart::display('source/list.tpl');
