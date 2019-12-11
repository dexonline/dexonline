<?php

$urlName = Request::get('urlName');

$source = Source::get_by_urlName($urlName);
if (!$source) {
  FlashMessage::add(_('No source exists with the given ID.'));
  Util::redirectToHome();
}

Smart::assign('source', $source);
Smart::display('source/view.tpl');
