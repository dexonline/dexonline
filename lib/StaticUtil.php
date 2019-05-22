<?php

/**
 * Utility functions for working with the static server.
 **/

class StaticUtil {

  static function generateStaticFileList() {
    chdir(Config::STATIC_PATH);
    exec('find * | sort > fileList.txt');
  }

  static function ensureThumb($src, $dest, $size) {
    $fullSrc = Config::STATIC_PATH . $src;
    $fullDest = Config::STATIC_PATH . $dest;
    if (!file_exists($fullDest)) {
      @mkdir(dirname($fullDest), 0777, true);
      $command = sprintf(
        "convert -strip -geometry %sx%s -sharpen 1x1 '%s' '%s'",
        $size, $size, $fullSrc, $fullDest);
      OS::execute($command);
    }
  }

  // $src is a file anywhere on the disk, while $dest is a subpath of the static server
  static function copy($src, $dest) {
    $fullDest = Config::STATIC_PATH . $dest;
    @mkdir(dirname($fullDest), 0777, true);
    copy($src, $fullDest);
  }

  // similar to copy, but also deletes $src
  static function move($src, $dest) {
    self::copy($src, $dest);
    unlink($src);
  }

  static function putContents(&$contents, $dest) {
    $fullDest = Config::STATIC_PATH . $dest;
    @mkdir(dirname($fullDest), 0777, true);
    file_put_contents($fullDest, $contents);
  }

  static function delete($file) {
    @unlink(Config::STATIC_PATH . $file);
  }

}
