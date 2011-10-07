<?php

require_once("../phplib/util.php");

$dir = util_getRootPath() . pref_getServerPreference('pageCacheDir');
$maxCreationTime = time() - pref_getServerPreference('pageCacheExpiration') * 3600;
log_scriptLog("Running expirePageCache on dir {$dir} with timestamp limit {$maxCreationTime}");
$numDeleted = recursiveScan($dir, $maxCreationTime);
log_scriptLog("Running expirePageCache completed. Deleted {$numDeleted} files and directories");

/**********************************************************************************/

/**
 * Scans a directory recursively and deletes all files created before $maxCreationTime.
 * Also deletes empty directories.
 **/
function recursiveScan($path, $maxCreationTime) {
  $result = 0;
  $files = scandir($path);
  
  foreach ($files as $file) {
    if ($file == '.' || $file == '..') {
      continue;
    }
    $full = "$path/$file";

    if (is_dir($full)) {
      $result += recursiveScan($full, $maxCreationTime);
      if (@rmdir($full)) {
        $result++;
      }
    } else if (is_file($full)) {
      if (filemtime($full) < $maxCreationTime) {
        @unlink($full);
        $result++;
      }
    }
  }
  return $result;
}

?>
