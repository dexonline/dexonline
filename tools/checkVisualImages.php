<?php
/**
 * This script reports problems with the Visual images:
 *
 * - images without thumbnails;
 * - thumbnails without images;
 * - images referred in the Visual table that don't exist on the static server;
 * - images on the static server that don't appear in the Visual table.
 **/

require_once __DIR__ . '/../phplib/Core.php';

const IMG_PREFIX = 'img/visual/';
const THUMB_PREFIX = 'img/visual/thumb/';

const IGNORED = [ 'thumb' ];

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

  if (isLetterDirectory($file)) {
    Log::debug("Ignoring directory: {$file}");
  } else if (Str::startsWith($file, THUMB_PREFIX)) {
    $thumbs[substr($file, strlen(THUMB_PREFIX))] = 1;
  } else if (Str::startsWith($file, IMG_PREFIX)) {
    $imgs[substr($file, strlen(IMG_PREFIX))] = 1;
  } else {
    // Ignore files outside the img/visual/ directory
  }
}

foreach (IGNORED as $i) {
  unset($imgs[$i]);
}

// Grab images referred by Visual DB records.
$used = [];
$visuals = Model::factory('Visual')->find_result_set();
foreach ($visuals as $v) {
  $used[$v->path] = 1;
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

// Report images in Visual records that don't exist on the static server.
foreach ($used as $u => $ignored) {
  if (!isset($imgs[$u])) {
    print "Missing image reference: {$u}\n";
  }
}

// Report images on the static server that aren't used in Visual records
foreach ($imgs as $img => $ignored) {
  if (!isset($used[$img])) {
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
  $size = Visual::THUMB_SIZE;
  $tempDir = Core::getTempPath();

  if (in_array($extension, [ 'gif', 'jpeg', 'jpg', 'png' ])) {
    Log::info("Generating {$size}x{$size} thumbnail for $img");
    $url = Config::get('static.url') . IMG_PREFIX . $img;
    Log::info("Fetching $url");

    OS::executeAndAssert("rm -f {$tempDir}/a.{$extension} {$tempDir}/t.{$extension}");
    OS::executeAndAssert("wget -q -O {$tempDir}/a.{$extension} '$url'");

    OS::executeAndAssert(
      "convert -strip -geometry {$size}x{$size} -sharpen 1x1 " .
      "{$tempDir}/a.{$extension} {$tempDir}/t.{$extension}");


    if ($extension == 'png') {
      OS::executeAndAssert("optipng {$tempDir}/t.png");
    }

    Log::info("FTP upload: {$tempDir}/t.{$extension} => " . Config::get('static.url') . THUMB_PREFIX . $img);
    $ftp->staticServerPut("{$tempDir}/t.{$extension}", THUMB_PREFIX . $img);
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

function isLetterDirectory($file) {
  return preg_match("#/[a-z]$#", $file);
}
