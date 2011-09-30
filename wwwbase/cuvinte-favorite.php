<?php
require_once("../phplib/util.php");

$user = session_getUser();
if (!$user) {
  util_redirect('login');
}

smarty_assign('page_title', 'Cuvinte favorite');
smarty_assign('bookmarks', UserWordBookmarkDisplayObject::getByUser($user->id));
smarty_displayCommonPageWithSkin('cuvinte-favorite.ihtml');
?>
