<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();

$WOTD_IMAGE_DIR = realpath(__DIR__ . '/../img/wotd/');
$EXTENSIONS = array('jpg', 'jpeg', 'png', 'gif');

$query = util_getRequestParameter('q');

$files = recursiveScan($WOTD_IMAGE_DIR, $query);

foreach ($files as $file) {
  print("$file\n");
}

/*************************************************************************/

function recursiveScan($path, $query) {
  global $WOTD_IMAGE_DIR, $EXTENSIONS;
  $files = scandir($path);
  $results = array();
  
  foreach ($files as $file) {
    if ($file == '.' || $file == '..' || $file == '.svn' || $file == '.tmb') {
      continue;
    }
    $full = "$path/$file";

    if (is_dir($full)) {
      $results += recursiveScan($full, $query);
    } else {
      $extension = pathinfo(strtolower($full), PATHINFO_EXTENSION);
      if (in_array($extension, $EXTENSIONS)) {
        $candidate = substr($full, strlen($WOTD_IMAGE_DIR) + 1);
        if (preg_match("/{$query}/i", $candidate)) {
          $results[] = $candidate;
        }
      }
    }
  }
  return $results;
}


?>
