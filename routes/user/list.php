<?php

User::mustHave(User::PRIV_ADMIN);

$userIds = Request::getArray('userIds');
$newNick = Request::get('newNick');
$newPriv = Request::getArray('newPriv');
$saveButton = Request::has('saveButton');

if ($saveButton) {
  foreach ($userIds as $userId) {
    $user = User::get_by_id($userId);
    $privs = Request::getArray("priv_{$userId}");
    $newPerm = array_sum($privs);

    if ($newPerm != $user->moderator) {
      Log::warning("Changed permissions for user {$user->id} ({$user->nick}) " .
                   "from {$user->moderator} to {$newPerm}");
      $user->moderator = $newPerm;
      $user->save();
    }
  }

  if ($newNick) {
    $user = User::get_by_nick($newNick);
    if ($user) {
      $user->moderator = array_sum($newPriv);
      $user->save();
      Log::warning("Granted permissions {$user->moderator} to user {$user->id} ({$user->nick})");
    } else {
      FlashMessage::add("Numele de utilizator „{$newNick}” nu există.");
      Util::redirectToRoute('user/list');
    }
  }

  FlashMessage::add('Am salvat modificările.', 'success');
  Util::redirectToRoute('user/list');
}

$moderators = Model::factory('User')
  ->where_not_equal('moderator', 0)
  ->order_by_asc('nick')
  ->find_many();

Smart::assign('users', $moderators);
Smart::addResources('admin');
Smart::display('user/list.tpl');
