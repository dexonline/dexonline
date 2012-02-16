<?php

/**
 * Takes a directory containing images and attempts to optimize them to reduce their size.
 * For each image file filename.ext:
 *
 * 1. convert --strip filename.ext filename2.ext
 * 2. convert filename2.ext filename3.png
 * 3. optipng filename3.png
 * 4. Keep the minimum size file from filename.ext, filename2.ext, filename3.png. Discard the others
 * 5. If there was an optimization, save it as filename.ext (keep the filename, possibly change the extension).
 **/

require_once __DIR__ . '/../phplib/util.php';

$IGNORED_DIRS = array('.', '..', '.svn');
$EXTENSIONS = array('gif', 'jpeg', 'jpg', 'png');

if (count($argv) != 2) {
  die("Usage: php {$argv[0]} <directory>\n");
}

$dir = realpath($argv[1]);
if (!$dir || !is_dir($dir)) {
  die("Directory {$argv[1]} does not exist.\n");
}

$beforeBytes = 0;
$afterBytes = 0;
recursiveScan($dir);

$compression = 100.0 * (1 - $afterBytes/$beforeBytes);
printf("\nTotal: %d/%d bytes, %.2f%% saved\n", $afterBytes, $beforeBytes, $compression);

/**************************************************************************/

function recursiveScan($path) {
  global $IGNORED_DIRS, $EXTENSIONS, $beforeBytes, $afterBytes;
  $files = scandir($path);
  
  foreach ($files as $file) {
    if (in_array($file, $IGNORED_DIRS)) {
      continue;
    }
    $full = "$path/$file";

    if (is_dir($full)) {
      recursiveScan($full);
    } else {
      $extension = pathinfo(strtolower($full), PATHINFO_EXTENSION);
      $fullNoExt = substr($full, 0, strlen($full) - strlen($extension) - 1); // Strip the dot as well
      if (in_array($extension, $EXTENSIONS)) {
        OS::executeAndAssert("convert -strip '{$full}' '/tmp/fileNoExif.{$extension}'");
        OS::executeAndAssert("convert '/tmp/fileNoExif.{$extension}' '/tmp/fileNoExifPng.png'");
        OS::executeAndAssert("optipng '/tmp/fileNoExifPng.png'");
        $fs1 = filesize($full);
        $fs2 = filesize("/tmp/fileNoExif.{$extension}");
        $fs3 = filesize('/tmp/fileNoExifPng.png');
        $beforeBytes += $fs1;
        if ($fs3 < $fs1 && $fs3 < $fs2) {
          $compression = 100.0 * (1 - $fs3/$fs1);
          $afterBytes += $fs3;
          printf("%s -- Strip EXIF, convert to PNG and optimize: %d/%d bytes, %.2f%% saved\n", $full, $fs3, $fs1, $compression);
          unlink($full);
          unlink("/tmp/fileNoExif.{$extension}");
          rename('/tmp/fileNoExifPng.png', "$fullNoExt.png");
        } else if ($fs2 < $fs1) {
          $compression = 100.0 * (1 - $fs2/$fs1);
          $afterBytes += $fs2;
          printf("%s -- Strip EXIF: %d/%d bytes, %.2f%% saved\n", $full, $fs2, $fs1, $compression);
          unlink($full);
          rename("/tmp/fileNoExif.{$extension}", $full);
          unlink('/tmp/fileNoExifPng.png');
        } else {
          $afterBytes += $fs1;
          print "{$full} -- leave unchanged\n";
          unlink("/tmp/fileNoExif.{$extension}");
          unlink('/tmp/fileNoExifPng.png');
        }
      }
    }
  }
}

?>
