<?php
/**
 * This script reports problems with the Visual images:
 *
 * - images without thumbnails;
 * - thumbnails without images;
 * - images referred in the Visual table that don't exist on the static server;
 * - images on the static server that don't appear in the Visual table.
 **/

require_once __DIR__ . '/../lib/Core.php';

const IGNORED = [ 'thumb' ];

$opts = getopt('', ['fix']);
$fix = isset($opts['fix']);

$staticFiles = file(Config::STATIC_PATH . 'fileList.txt');

// Grab images and thumbs from the static server file list.
$imgs = [];
$thumbs = [];

foreach ($staticFiles as $file) {
  $file = trim($file);

  if (isLetterDirectory($file)) {
    Log::debug("Ignoring directory: {$file}");
  } else if (Str::startsWith($file, Visual::STATIC_THUMB_DIR)) {
    $thumbs[substr($file, strlen(Visual::STATIC_THUMB_DIR))] = 1;
  } else if (Str::startsWith($file, Visual::STATIC_DIR)) {
    $imgs[substr($file, strlen(Visual::STATIC_DIR))] = 1;
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

// Report images without thumbnails.
foreach ($imgs as $img => $ignored) {
  if (!isset($thumbs[$img])) {
    print "Image without a thumbnail: {$img}\n";
    if ($fix) {
      generateThumbnail($img);
    }
  }
}

// Report thumbnails without images (orphan thumbnails).
foreach ($thumbs as $thumb => $ignored) {
  if (!isset($imgs[$thumb])) {
    print "Thumbnail without an image: {$thumb}\n";
    if ($fix) {
      deleteOrphanThumbnail($thumb);
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

function generateThumbnail($img) {
  $extension = @pathinfo($img)['extension']; // may be missing entirely
  $extension = strtolower($extension);
  $size = Visual::THUMB_SIZE;

  if (in_array($extension, [ 'gif', 'jpeg', 'jpg', 'png' ])) {
    Log::info("Generating {$size}x{$size} thumbnail for $img");
    StaticUtil::ensureThumb(
      Visual::STATIC_DIR . $img,
      Visual::STATIC_THUMB_DIR . $img,
      $size);
  }
}

function deleteOrphanThumbnail($thumb) {
  $extension = @pathinfo($thumb)['extension']; // may be missing entirely
  $extension = strtolower($extension);

  if (in_array($extension, [ 'gif', 'jpeg', 'jpg', 'png' ])) {
    Log::info("Deleting %s%s", Visual::STATIC_THUMB_DIR, $thumb);
    StaticUtil::delete(Visual::STATIC_THUMB_DIR . $thumb);
  }
}

function isLetterDirectory($file) {
  return preg_match("#/[a-z]$#", $file);
}
