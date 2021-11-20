<?php

$nick = Request::get('nick');
$userId = Request::get('userId');
$medalsGranted = Request::get('medalsGranted');
$medalSaveButton = Request::has('medalSaveButton');

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

$user->email = Str::scrambleEmail($user->email);

// find number of WotD images drawn
$topArtists = UserStats::getTopArtists();
$rank = lookup($user->id, $topArtists, 'id');
$numImages = ($rank !== false) ? $topArtists[$rank]['c'] : 0;

if (User::can(User::PRIV_ADMIN)) {
  // Admins can grant/revoke medals
  Smart::assign('allMedals', Medal::getData());
}
Smart::assign([
  'medals' => Medal::loadForUser($user),
  'numImages' => $numImages,
  'topEntry' => TopEntry::getForUser($user->id),
  'user' => $user,
]);
Smart::display('user/view.tpl');

/*************************************************************************/

// Syntactic sugar. Returns the rank of $value in $array[0][$column],
// $array[1][$column], ...
function lookup($value, $array, $column) {
  return array_search($value, array_column($array, $column));
}
