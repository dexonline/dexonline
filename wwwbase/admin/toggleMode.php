<?php

require_once("../../phplib/Core.php");

$mode = Request::get('mode');

switch ($mode) {
  case 'wotd':
    User::mustHave(User::PRIV_WOTD);
    Session::toggleWotdMode();
    break;

  case 'diffLevel':
    User::mustHave(User::PRIV_ADMIN);
    Session::toggleWordHistoryDiffSplitLevel();
    break;
}

$target = isset($_SERVER['HTTP_REFERER'])
        ? $_SERVER['HTTP_REFERER']
        : Core::getWwwRoot();
Util::redirect($target);

?>
