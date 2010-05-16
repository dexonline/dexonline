<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$userIds = util_getRequestParameter('userIds');
$newNick = util_getRequestParameter('newNick');
$newCheckboxes = util_getRequestParameterWithDefault("newPriv", array());
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  foreach ($userIds as $userId) {
    $checkboxes = util_getRequestParameterWithDefault("priv_$userId", array());
    $user = User::get("id = $userId");
    $user->moderator = array_sum($checkboxes);
    $user->save();
  }

  if ($newNick) {
    $user = User::get("nick = '$newNick'");
    if ($user) {
      $user->moderator = array_sum($newCheckboxes);
      $user->save();
    } else {
      session_setFlash("Numele de utilizator „{$newNick}” nu există");
      util_redirect("moderatori");
    }
  }

  session_setFlash('Modificările au fost salvate', 'info');
  util_redirect("moderatori");
}

smarty_assign('page_title', 'Moderatori');
smarty_assign('users', db_find(new User(), "moderator != 0 order by nick"));
smarty_displayCommonPageWithSkin('moderatori.ihtml');

?>
