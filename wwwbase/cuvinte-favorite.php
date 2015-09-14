<?php
require_once("../phplib/util.php");

$user = session_getUser();
if (!$user) {
  util_redirect('auth/login');
}

SmartyWrap::assign('page_title', 'Cuvinte favorite');
SmartyWrap::assign('bookmarks', UserWordBookmarkDisplayObject::getByUser($user->id));
SmartyWrap::display('cuvinte-favorite.tpl');
?>
