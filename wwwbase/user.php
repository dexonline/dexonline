<?php
require_once("../phplib/util.php");

// Parse or initialize the GET/POST arguments
$nick = Request::get('n');
$medalSaveButton = Request::get('medalSaveButton');
$userId = Request::get('userId');
$medalsGranted = Request::get('medalsGranted');

if ($medalSaveButton) {
  util_assertModerator(PRIV_ADMIN);
  $user = User::get_by_id($userId);
  $user->medalMask = Medal::getCanonicalMask(array_sum($medalsGranted));
  $user->save();
  util_redirect(util_getWwwRoot() . "utilizator/{$user->nick}");
}

$user = User::get_by_nick($nick);
if (!$user) {
  FlashMessage::add('Utilizatorul ' . htmlspecialchars($nick) . ' nu există.');
  util_redirect(util_getWwwRoot());
}

$userData = array();
$user->email = StringUtil::scrambleEmail($user->email);

// Find the rank of this user by number of words and number of characters
$topWords = TopEntry::getTopData(CRIT_WORDS, SORT_DESC, true);
$numUsers = count($topWords);
$rankWords = 0;
while ($rankWords < $numUsers && $topWords[$rankWords]->userNick != $nick) {
  $rankWords++;
}

$userData['rank_words'] = $rankWords + 1;
if ($rankWords < $numUsers) {
  $topEntry = $topWords[$rankWords];
  $userData['last_submission'] = $topEntry->timestamp;
  $userData['num_words'] = $topEntry->numDefinitions;
  $userData['num_chars'] = $topEntry->numChars;
}

$topChars = TopEntry::getTopData(CRIT_CHARS, SORT_DESC, true);
$numUsers = count($topChars);
$rankChars = 0;
while ($rankChars < $numUsers && $topChars[$rankChars]->userNick != $nick) {
  $rankChars++;
}

$userData['rank_chars'] = $rankChars + 1;
SmartyWrap::assign('medals', Medal::loadForUser($user));
if (util_isModerator(PRIV_ADMIN)) {
  // Admins can grant/revoke medals
  SmartyWrap::assign('allMedals', Medal::$DATA);
}
SmartyWrap::assign('user', $user);
SmartyWrap::assign('userData', $userData);
SmartyWrap::display('user.tpl');
?>
