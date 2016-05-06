<?php
/**
 * This script reports problems with the WotD images:
 *
 * - images without thumbnails;
 * - thumbnails without images;
 * - images referred in the WordOfTheDay table that don't exist on the
 *   static server;
 * - images on the static server that don't appear in the WordOfTheDay
 *   table;
 **/

require_once __DIR__ . '/../phplib/util.php';

define('IMG_PREFIX', 'img/wotd/');
define('THUMB_PREFIX', 'img/wotd/thumb/');
$IGNORED_PREFIXES = [
  'img/wotd/cuvantul-lunii',
];

$fix = false;
foreach ($argv as $i => $arg) {
  if ($i) {
    switch ($arg) {
      case '--fix': $fix = true; break;
      default: print "Unknown flag $arg -- aborting\n"; exit;
    }
  }
}

$staticFiles = file(Config::get('static.url') . 'fileList.txt');

$imgs = [];
$thumbs = [];

foreach ($staticFiles as $file) {
  $file = trim($file);

  $filtered = false;
  foreach ($IGNORED_PREFIXES as $prefix) {
    $filtered |= StringUtil::startsWith($file, $prefix);
  }

  if (!$filtered) {
    // best-effort test to discern files from directories
    //  if (preg_match('/.*\.[a-z]+/i', $file)) {
    if (StringUtil::startsWith($file, THUMB_PREFIX)) {
      $thumbs[substr($file, strlen(THUMB_PREFIX))] = 1;
    } else if (StringUtil::startsWith($file, IMG_PREFIX)) {
      $imgs[substr($file, strlen(IMG_PREFIX))] = 1;
    }
  }
}

foreach ($imgs as $img => $ignored) {
  if (!isset($thumbs[$img])) {
    print "Image without a thumbnail: {$img}\n";
    if ($fix) {
      generateThumbnail($img);
    }
  }
}

// TODO: more tests

/*************************************************************************/

function generateThumbnail($img) {
  $extension = @pathinfo($img)['extension']; // may be missing entirely
  $extension = strtolower($extension);

  if (in_array($extension, [ 'jpeg', 'jpg', 'png' ])) {
    Log::info("Generating thumbnail for $img");
    $url = Config::get('static.url') . IMG_PREFIX . $img;
    Log::info("Fetching $url");

    OS::executeAndAssert("rm -f /tmp/a.{$extension} /tmp/t.{$extension}");
    OS::executeAndAssert("wget -q -O /tmp/a.{$extension} '$url'");
    OS::executeAndAssert("convert -strip -geometry 48x48 -sharpen 1x1 a.{$extension} t.{$extension}");
    // TODO: FTP upload
  }
}
