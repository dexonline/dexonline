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
define('UNUSED_PREFIX', 'nefolosite/');
$IGNORED_PREFIXES = [
  'img/wotd/cuvantul-lunii',
  'img/wotd/misc/aleator.jpg', // random word icon
  'img/wotd/misc/papirus.png', // article of the month icon
  'img/wotd/generic.jpg',      // no image icon
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

  $filtered = false;
  foreach ($IGNORED_PREFIXES as $prefix) {
    $filtered |= StringUtil::startsWith($file, $prefix);
  }

  if (!$filtered) {
    // Filter /YYYY and /YYYY/MM directory names
    if (preg_match("#^" . IMG_PREFIX . "[0-9]{4}(/[0-9]{2})?$#", $file) ||
        preg_match("#^" . THUMB_PREFIX . "[0-9]{4}(/[0-9]{2})?$#", $file)) {
      Log::debug("Ignoring directory: {$file}");
    } else {
      if (StringUtil::startsWith($file, THUMB_PREFIX)) {
        $thumbs[substr($file, strlen(THUMB_PREFIX))] = 1;
      } else if (StringUtil::startsWith($file, IMG_PREFIX)) {
        $imgs[substr($file, strlen(IMG_PREFIX))] = 1;
      }
    }
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

$ftp = new FtpUtil();

// Report images without thumbnails.
foreach ($imgs as $img => $ignored) {
  if (!isset($thumbs[$img])) {
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
      !StringUtil::startsWith($img, UNUSED_PREFIX)) {
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

  if (in_array($extension, [ 'jpeg', 'jpg', 'png' ])) {
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

  $extension = @pathinfo($img)['extension']; // may be missing entirely
  $extension = strtolower($extension);

  if (in_array($extension, [ 'jpeg', 'jpg', 'png' ])) {
    Log::info("Deleting %s%s", THUMB_PREFIX, $thumb);
    $ftp->staticServerDelete(THUMB_PREFIX . $thumb);
  }
}
