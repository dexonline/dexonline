<?php

$id = Request::get('id');

$source = Source::get_by_id($id);
if (!$source) {
  FlashMessage::add(_('No source exists with the given ID'));
  Util::redirectToHome();
}

Smart::assign('source', $source);
Smart::display('source/view.tpl');
