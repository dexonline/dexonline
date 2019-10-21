<?php

$nick = Request::get('nick');
$medalSaveButton = Request::get('medalSaveButton');
$userId = Request::get('userId');
$medalsGranted = Request::get('medalsGranted');

if ($medalSaveButton) {
  User::mustHave(User::PRIV_ADMIN);
  $user = User::get_by_id($userId);
  $user->medalMask = Medal::getCanonicalMask(array_sum($medalsGranted));
  $user->save();
  Util::redirectToSelf(); // including /nick
}

$user = User::get_by_nick($nick);
if (!$user) {
  FlashMessage::add('Utilizatorul ' . htmlspecialchars($nick) . ' nu există.');
  Util::redirectToHome();
}

$userData = [];
$user->email = Str::scrambleEmail($user->email);

// find number of WotD images drawn
$topArtists = FileCache::getArtistTop();
if (!$topArtists) {
  $topArtists = UserStats::getTopArtists();
  FileCache::putArtistTop($topArtists);
}
$rank = lookup($user->id, $topArtists, 'id');
$userData['num_images'] = ($rank === false) ? 0 : $topArtists[$rank]['c'];

// find number of definitions and characters submitted and respective ranks
$topWords = TopEntry::getTopData(TopEntry::SORT_WORDS, SORT_DESC, true);
$rank = lookup($user->nick, $topWords, 'userNick');
if ($rank !== false) {
  $row = $topWords[$rank];
  $userData['rank_words'] = $rank + 1;
  $userData['last_submission'] = $row->timestamp;
  $userData['num_words'] = $row->numDefinitions;
  $userData['num_chars'] = $row->numChars;

  // also look up the rank by characters in the respective top
  $topChars = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);
  $userData['rank_chars'] = 1 + lookup($user->nick, $topChars, 'userNick');
}

Smart::assign('medals', Medal::loadForUser($user));
if (User::can(User::PRIV_ADMIN)) {
  // Admins can grant/revoke medals
  Smart::assign('allMedals', Medal::getData());
}
Smart::assign('user', $user);
Smart::assign('userData', $userData);
Smart::display('user/view.tpl');

/*************************************************************************/

// Syntactic sugar. Returns the rank of $value in $array[0][$column],
// $array[1][$column], ...
function lookup($value, $array, $column) {
  return array_search($value, array_column($array, $column));
}
