<?php
require_once("../phplib/util.php");

// Parse or initialize the GET/POST arguments
$nick = util_getRequestParameter('n');
$user = User::get_by_nick($nick);

$userData = array();
if ($user) {
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
  SmartyWrap::assign('page_title', "Utilizator: $nick");
} else {
  SmartyWrap::assign('missingNick', $nick);
  SmartyWrap::assign('page_title', 'Utilizator inexistent');
}

SmartyWrap::assign('user', $user);
SmartyWrap::assign('userData', $userData);
SmartyWrap::displayCommonPageWithSkin('user.ihtml');
?>
