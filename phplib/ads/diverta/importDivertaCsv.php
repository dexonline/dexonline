<?php
// TODO: Convert to Idiorm if this is ever needed again
require_once('phplib/Core.php');
require_once('phplib/ads/adsModule.php');
require_once('phplib/ads/diverta/divertaAdsModule.php');
define('IMG_URL_PREFIX', 'http://www.dol.ro/wcsstore/DOL/');
define('ORIG_FILE_PREFIX', Config::get('global.tempDir') . '/dol/big/');
define('THUMB_PREFIX', Core::getRootPath() . 'wwwbase/img/diverta/thumb/');
define('IMG_NORMAL', 0);
define('IMG_NOT_JPEG', 1);
define('IMG_CORRUPT', 2);

$opts = getopt('f:h:d:s:t:a:p:u:i:o:');
if (count($opts) != 10) {
  usage();
}
define('CSV_DELIMITER', getDelimiter($opts['d']));
OS::executeAndAssert('mkdir -p ' . ORIG_FILE_PREFIX);

if (!file_exists($opts['f'])) {
  print "Input file does not exist.\n";
  exit();
}
$handle = fopen($opts['f'], "r");
$i = 0;
while (($fields = fgetcsv($handle, 10000, CSV_DELIMITER)) !== false) {
  $i++;
  if ($i <= $opts['h']) {
    continue;
  }
  $sku = $fields[$opts['s']];
  print "Line $i: [$sku]\n";

  // Reuse the record or create a new one
  $book = DivertaBook::get("sku = '{$sku}'");
  if ($book) {
    print "  Reusing book id = {$book->id}\n";
  } else {
    print "  Creating new book\n";
    $book = new DivertaBook();
    $book->sku = $sku;
    $book->impressions = 0;
    $book->clicks = 0;
  }
  if (overwrite($book, $opts, 't')) {
    $book->title = $fields[$opts['t']];
  }
  if (overwrite($book, $opts, 'a')) {
    $book->author = $fields[$opts['a']];
  }
  if (overwrite($book, $opts, 'p')) {
    $book->publisher = $fields[$opts['p']];
  }
  if (overwrite($book, $opts, 'i')) {
    $book->imageUrl = IMG_URL_PREFIX . $fields[$opts['i']];
  }
  if (overwrite($book, $opts, 'u')) {
    $book->url = $fields[$opts['u']];
  }
  $book->save();
  print "  [{$book->title}] by [{$book->author}]\n";

  // Grab the image unless we already have it
  $fileName = ORIG_FILE_PREFIX . "{$sku}.jpg";
  $thumbName = THUMB_PREFIX . "{$sku}.jpg";
  if (file_exists($fileName)) {
    $haveFile = true;
  } else {
    $haveFile = false;
    $img = Util::fetchUrl($book->imageUrl);
    if ($img !== false) {
      // Dump the image to a file
      $file = fopen ($fileName, "w");
      fwrite($file, $img);
      fclose ($file);
      $haveFile = true;
    }
  }

  $alreadyResized = file_exists($thumbName);
  if ($haveFile && !$alreadyResized) {
    $imgType = isImage($fileName);
    if ($imgType == IMG_NORMAL) {
      list ($width, $height, $bytes) = preg_split('/\|/', getImageInfo($fileName));
      print "  {$width}x{$height}, {$bytes} bytes ";
      OS::executeAndAssert("convert -trim -fuzz \"3%\" -geometry 200x84 \"$fileName\" \"$thumbName\"");
      if ($width <= 90 && $height <= 90) {
        print "*small* ";
      }
      list ($thumbWidth, $thumbHeight, $ignored) = preg_split('/\|/', getImageInfo($thumbName));
      $book->thumbWidth = $thumbWidth;
      $book->thumbHeight = $thumbHeight;
      $book->save();
    } else if ($imgType == IMG_NOT_JPEG) {
      print "  Not an image ";
    } else {
      print "  Corrupted image ";
    }
  } elseif ($alreadyResized) {
    list ($thumbWidth, $thumbHeight, $ignored) = preg_split('/\|/', getImageInfo($thumbName));
    $book->thumbWidth = $thumbWidth;
    $book->thumbHeight = $thumbHeight;
    $book->save();
    print "  Already processed, skipping ";
  } else {
    print "  Cannot fetch image! ";
  }
  print "\n";
}

function getImageInfo($fileName) {
  $output = OS::executeAndReturnOutput("identify -format \"%w|%h|%b\" \"$fileName\"");
  assert(count($output) == 1);
  return $output[0];
}

function isImage($fileName) {
  // Check that the image exists
  $output = OS::executeAndReturnOutput("file \"$fileName\"");
  assert(count($output) == 1);
  if (strpos($output[0], 'JPEG image data') === false) {
    return IMG_NOT_JPEG;
  }

  $output = OS::executeAndReturnOutput("identify -verbose \"$fileName\" 2>&1 1>/dev/null");
  if (count($output)) {
    return IMG_CORRUPT; // because there are warnings
  }
  return IMG_NORMAL;
}

function usage() {
  print "Required arguments:\n";
  print "--f (file)         input file\n";
  print "--h (header-rows)  number of header rows to discard\n";
  print "--d (delim)        CSV delimiter (one of the strings 'tab', 'comma' or 'space'\n";
  print "--s (sku)          column number for the sku (all column numbers are 0-based)\n";
  print "--t (title)        column number for the title\n";
  print "--a (author)       column number for the author\n";
  print "--p (publisher)    column number for the publisher\n";
  print "--u (url)          column number for the book URL\n";
  print "--i (image-url)    column number for the image URL\n";
  print "--o (overwrite)    fields to overwrite ([t]itle, [a]uthor, [p]ublisher,\n";
  print "                   [u]rl, [i]mage-url)\n";
  exit(1);
}

function getDelimiter($string) {
  switch ($string) {
    case 'tab': return "\t";
    case 'comma': return ',';
    case 'space': return ' ';
    default: print "-d can be one of the strings 'tab', 'comma' or 'space'\n"; exit(1);
  }
}

function overwrite($book, $opts, $letter) {
  // Always allow overwrites when we create a new Book.
  return (!$book->id) || (strpos($opts['o'], $letter) !== false);
}

?>
