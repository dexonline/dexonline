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
    $user = User::get_by_id($userId);
    $user->moderator = array_sum($checkboxes);
    $user->save();
  }

  if ($newNick) {
    $user = User::get_by_nick($newNick);
    if ($user) {
      $user->moderator = array_sum($newCheckboxes);
      $user->save();
    } else {
      FlashMessage::add("Numele de utilizator „{$newNick}” nu există");
      util_redirect("moderatori");
    }
  }

  FlashMessage::add('Modificările au fost salvate', 'info');
  util_redirect("moderatori");
}

SmartyWrap::assign('page_title', 'Moderatori');
SmartyWrap::assign('users', Model::factory('User')->where_not_equal('moderator', 0)->order_by_asc('nick')->find_many());
SmartyWrap::displayCommonPageWithSkin('moderatori.ihtml');

?>
