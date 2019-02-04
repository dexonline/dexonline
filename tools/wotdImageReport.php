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

require_once __DIR__ . '/../lib/Core.php';

const IMG_PREFIX = 'img/wotd/';
const THUMB_PREFIX = 'img/wotd/thumb%s/';
const UNUSED_PREFIX = 'nefolosite/';
const THUMB_SIZES = WordOfTheDay::THUMBNAIL_SIZES;

$ignoredMap = [
  'cuvantul-lunii' => 1,
  'cuvantul-lunii/generic.jpg' => 1,
  'generic.jpg' => 1,
  'misc' => 1,
  'misc/aleator.jpg' => 1,
  'misc/papirus.png' => 1,
  'nefolosite' => 1,
];
foreach (THUMB_SIZES as $size) {
  $ignoredMap['thumb' . $size] = 1;
}

$opts = getopt('', ['fix']);
$fix = isset($opts['fix']);

$staticFiles = file(Config::STATIC_URL . 'fileList.txt');
if (!$staticFiles) {
  OS::errorAndExit('Could not load the static files list.');
}

// Grab images and thumbs from the static server file list.
$imgs = [];
$thumbs = [];
foreach (THUMB_SIZES as $size) {
  $thumbs[$size] = [];
}

foreach ($staticFiles as $file) {
  $file = trim($file);

  if (isMonthYearDirectory($file)) {
    Log::debug("Ignoring directory: {$file}");
  } else {

    $isThumb = false;
    foreach (THUMB_SIZES as $size) {
      $prefix = sprintf(THUMB_PREFIX, $size);
      if (Str::startsWith($file, $prefix)) {
        $thumbs[$size][substr($file, strlen($prefix))] = 1;
        $isThumb = true;
      }
    }

    if (!$isThumb && Str::startsWith($file, IMG_PREFIX)) {
      $imgs[substr($file, strlen(IMG_PREFIX))] = 1;
    } else {
      // Ignore files outside the img/wotd/ directory
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
  foreach (THUMB_SIZES as $size) {
    $prefix = sprintf(THUMB_PREFIX, $size);
    if (!isset($thumbs[$size][$img]) &&
        !isset($ignoredMap[$img])) {
      Log::warning("Image without a {$size}px thumbnail: {$img}");
      if ($fix) {
        generateThumbnail($ftp, $img, $size, $prefix);
      }
    }
  }
}

// Report thumbnails without images (orphan thumbnails).
foreach ($thumbs as $size => $thumbList) {
  $prefix = sprintf(THUMB_PREFIX, $size);
  foreach ($thumbList as $thumb => $ignored) {
    if (!isset($imgs[$thumb])) {
      Log::warning("{$size}px thumbnail without an image: {$thumb}");
      if ($fix) {
        deleteOrphanThumbnail($ftp, $thumb, $prefix);
      }
    }
  }
}

// Report images in WotD records that don't exist on the static server.
foreach ($used as $u => $ignored) {
  if (!isset($imgs[$u])) {
    Log::warning("Missing image reference: {$u}");
  }
}

// Report images on the static server that aren't used in WotD records
foreach ($imgs as $img => $ignored) {
  if (!isset($used[$img]) &&
      !Str::startsWith($img, UNUSED_PREFIX) &&
      !isset($ignoredMap[$img])) {
    Log::warning("Unused image: {$img}");
  }
}

/*************************************************************************/

function generateThumbnail($ftp, $img, $size, $prefix) {
  if (!$ftp->connected()) {
    Log::error("Cannot connect to FTP server - skipping thumb generation.");
    return;
  }

  $extension = @pathinfo($img)['extension']; // may be missing entirely
  $extension = strtolower($extension);
  $tempDir = Config::TEMP_DIR;

  if (in_array($extension, [ 'gif', 'jpeg', 'jpg', 'png' ])) {
    Log::info("Generating {$size}x{$size} thumbnail for $img");
    $url = Config::STATIC_URL . IMG_PREFIX . $img;
    Log::info("Fetching $url");

    OS::executeAndAssert("rm -f {$tempDir}a.{$extension} {$tempDir}t.{$extension}");
    OS::executeAndAssert("wget -q -O {$tempDir}a.{$extension} '$url'");

    $output = OS::executeAndReturnOutput("identify -format '%wx%h' {$tempDir}a.{$extension}");
    $resolution = $output[0];

    if ($resolution == "{$size}x{$size}") {
      copy("{$tempDir}a.{$extension}", "{$tempDir}t.{$extension}");
    } else {
      OS::executeAndAssert(
        "convert -strip -geometry {$size}x{$size} -sharpen 1x1 " .
        "{$tempDir}a.{$extension} {$tempDir}t.{$extension}");
    }

    if ($extension == 'png') {
      OS::executeAndAssert("optipng {$tempDir}t.png");
    }

    Log::info("FTP upload: {$tempDir}t.{$extension} => " . Config::STATIC_URL . $prefix . $img);
    $ftp->staticServerPut("{$tempDir}t.{$extension}", $prefix . $img);
  }
}

function deleteOrphanThumbnail($ftp, $thumb, $prefix) {
  if (!$ftp->connected()) {
    Log::error("Cannot connect to FTP server - skipping orphan thumb deletion.");
    return;
  }

  $extension = @pathinfo($thumb)['extension']; // may be missing entirely
  $extension = strtolower($extension);

  if (in_array($extension, [ 'gif', 'jpeg', 'jpg', 'png' ])) {
    Log::info("Deleting %s%s", $prefix, $thumb);
    $ftp->staticServerDelete($prefix . $thumb);
  }
}

function isMonthYearDirectory($file) {
  return preg_match("#[0-9]{4}(/[0-9]{2})?$#", $file);
}
