<?php

require_once("../../phplib/Core.php");

$mode = Request::get('mode');

switch ($mode) {
  case 'structure':
    User::mustHave(User::PRIV_STRUCT);
    Session::toggleMode('structureMode',
                        'Modul structurist activat. La salvarea unei intrări făra structurist, ' .
                        'veți fi trecut ca structurist.',
                        'Modul structurist dezactivat.');
    break;

  case 'wotd':
    User::mustHave(User::PRIV_WOTD);
    Session::toggleMode('wotdMode',
                        'Modul WotD activat.',
                        'Modul WotD dezactivat.');
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
