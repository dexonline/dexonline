<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();

define('MAX_RESULTS', 10);

$WOTD_IMAGE_DIR = realpath(__DIR__ . '/../img/wotd/');
$EXTENSIONS = array('jpg', 'jpeg', 'png', 'gif');
$IGNORED_DIRS = array('.', '..', '.tmb', 'thumb');

$query = util_getRequestParameter('term');

$files = recursiveScan($WOTD_IMAGE_DIR, $query);
$files = array_slice($files, 0, MAX_RESULTS);

$resp = array('results' => array());
foreach ($files as $key => $fileName) {
  $resp['results'][] = array('id' => $fileName, 'text' => $fileName);
}
print json_encode($resp);

/*************************************************************************/

function recursiveScan($path, $query) {
  global $WOTD_IMAGE_DIR, $EXTENSIONS, $IGNORED_DIRS;
  $files = scandir($path);
  $results = array();
  
  foreach (array_reverse($files) as $file) {
    if (in_array($file, $IGNORED_DIRS)) {
      continue;
    }
    $full = "$path/$file";

    if (is_dir($full)) {
      $results = array_merge($results, recursiveScan($full, $query));
    } else {
      $extension = pathinfo(strtolower($full), PATHINFO_EXTENSION);
      if (in_array($extension, $EXTENSIONS)) {
        $candidate = substr($full, strlen($WOTD_IMAGE_DIR) + 1);
        $noExtension = substr($candidate, 0, strpos($candidate, '.')); // strip the extension
        $regexp = '/' . str_replace('/', "\\/", $query) . '/i';
        if (preg_match($regexp, $noExtension)) {
          $results[] = $candidate;
        }
      }
    }
  }
  return $results;
}


?>
