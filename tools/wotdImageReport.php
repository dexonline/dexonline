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
define('WOTM_PREFIX', 'img/wotd/cuvantul-lunii/');
define('UNUSED_PREFIX', 'nefolosite/');
$IGNORED = [
  'cuvantul-lunii' => 1,
  'cuvantul-lunii/generic.jpg' => 1,
  'generic.jpg' => 1,
  'misc' => 1,
  'misc/aleator.jpg' => 1,
  'misc/papirus.png' => 1,
  'nefolosite' => 1,
  'thumb' => 1,
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

// Grab images and thumbs from the static server file list.
$imgs = [];
$thumbs = [];

foreach ($staticFiles as $file) {
  $file = trim($file);

  if (isMonthYearDirectory($file)) {
    Log::debug("Ignoring directory: {$file}");
  } else if (StringUtil::startsWith($file, THUMB_PREFIX)) {
    $thumbs[substr($file, strlen(THUMB_PREFIX))] = 1;
  } else if (StringUtil::startsWith($file, IMG_PREFIX)) {
    $imgs[substr($file, strlen(IMG_PREFIX))] = 1;
  } else {
    // Ignore files outside the img/wotd/ directory
  }
}

// Grab images referred by WordOfTheDay DB records.
$used = [];
$wotds = Model::factory('WordOfTheDay')->find_result_set();
foreach ($wotds as $w) {
  if ($w->image) {
    $used[$w->image] = 1;
  }
}

// Grab images referred by WordOfTheMonth DB records.
$wotms = Model::factory('WordOfTheMonth')->find_result_set();
foreach ($wotms as $w) {
  if ($w->image) {
    $file = "cuvantul-lunii/{$w->image}";
    $used[$file] = 1;
  }
}

$ftp = new FtpUtil();

// Report images without thumbnails.
foreach ($imgs as $img => $ignored) {
  if (!isset($thumbs[$img]) &&
      !isset($IGNORED[$img])) {
    print "Image without a thumbnail: {$img}\n";
    if ($fix) {
      generateThumbnail($ftp, $img);
    }
  }
}

// Report thumbnails without images (orphan thumbnails).
foreach ($thumbs as $thumb => $ignored) {
  if (!isset($imgs[$thumb])) {
    print "Thumbnail without an image: {$thumb}\n";
    if ($fix) {
      deleteOrphanThumbnail($ftp, $thumb);
    }
  }
}

// Report images in WotD records that don't exist on the static server.
foreach ($used as $u => $ignored) {
  if (!isset($imgs[$u])) {
    print "Missing image reference: {$u}\n";
  }
}

// Report images on the static server that aren't used in WotD records
foreach ($imgs as $img => $ignored) {
  if (!isset($used[$img]) &&
      !StringUtil::startsWith($img, UNUSED_PREFIX) &&
      !isset($IGNORED[$img])) {
    print "Unused image: {$img}\n";
  }
}

/*************************************************************************/

function generateThumbnail($ftp, $img) {
  if (!$ftp->connected()) {
    Log::error("Cannot connect to FTP server - skipping thumb generation.");
    return;
  }

  $extension = @pathinfo($img)['extension']; // may be missing entirely
  $extension = strtolower($extension);

  if (in_array($extension, [ 'gif', 'jpeg', 'jpg', 'png' ])) {
    Log::info("Generating thumbnail for $img");
    $url = Config::get('static.url') . IMG_PREFIX . $img;
    Log::info("Fetching $url");

    OS::executeAndAssert("rm -f /tmp/a.{$extension} /tmp/t.{$extension}");
    OS::executeAndAssert("wget -q -O /tmp/a.{$extension} '$url'");
    OS::executeAndAssert("convert -strip -geometry 48x48 -sharpen 1x1 /tmp/a.{$extension} /tmp/t.{$extension}");

    Log::info("FTP upload: /tmp/t.{$extension} => " . Config::get('static.url') . THUMB_PREFIX . $img);
    $ftp->staticServerPut("/tmp/t.{$extension}", THUMB_PREFIX . $img);
  }
}

function deleteOrphanThumbnail($ftp, $thumb) {
  if (!$ftp->connected()) {
    Log::error("Cannot connect to FTP server - skipping orphan thumb deletion.");
    return;
  }

  $extension = @pathinfo($thumb)['extension']; // may be missing entirely
  $extension = strtolower($extension);

  if (in_array($extension, [ 'gif', 'jpeg', 'jpg', 'png' ])) {
    Log::info("Deleting %s%s", THUMB_PREFIX, $thumb);
    $ftp->staticServerDelete(THUMB_PREFIX . $thumb);
  }
}

function isMonthYearDirectory($file) {
  return preg_match("#[0-9]{4}(/[0-9]{2})?$#", $file);
}
