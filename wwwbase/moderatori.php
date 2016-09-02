<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$userIds = util_getRequestParameter('userIds');
$newNick = util_getRequestParameter('newNick');
$newCheckboxes = util_getRequestParameterWithDefault("newPriv", array());
$saveButton = util_getBoolean('saveButton');

if ($saveButton) {
  foreach ($userIds as $userId) {
    $checkboxes = util_getRequestParameterWithDefault("priv_$userId", array());
    $user = User::get_by_id($userId);
    $newPerm = array_sum($checkboxes);

    if ($newPerm != $user->moderator) {
      Log::warning("Changed permissions for user {$user->id} ({$user->nick}) from {$user->moderator} to {$newPerm}");
    }

    $user->moderator = $newPerm;
    $user->save();
  }

  if ($newNick) {
    $user = User::get_by_nick($newNick);
    if ($user) {
      $user->moderator = array_sum($newCheckboxes);
      $user->save();
      Log::warning("Granted permissions {$user->moderator} to user {$user->id} ({$user->nick})");
    } else {
      FlashMessage::add("Numele de utilizator „{$newNick}” nu există");
      util_redirect("moderatori");
    }
  }

  FlashMessage::add('Am salvat modificările.', 'success');
  util_redirect('moderatori');
}

$moderators = Model::factory('User')->where_not_equal('moderator', 0)->order_by_asc('nick')->find_many();

SmartyWrap::assign('users', $moderators);
SmartyWrap::addCss('admin');
SmartyWrap::display('moderatori.tpl');

?>
