<?php

/**
 * Utility functions for working with the static server.
 **/

class StaticUtil {

  static function generateStaticFileList() {
    chdir(Config::STATIC_PATH);
    exec('find * | sort > fileList.txt');
  }

  static function mkdir($path) {
    @mkdir($path, 0777, true);
    chmod($path, 0777);
  }

  static function ensureThumb($src, $dest, $size) {
    $fullSrc = Config::STATIC_PATH . $src;
    $fullDest = Config::STATIC_PATH . $dest;
    if (!file_exists($fullDest)) {
      self::mkdir(dirname($fullDest));
      $command = sprintf(
        "convert -strip -geometry %sx%s -sharpen 1x1 '%s' '%s'",
        $size, $size, $fullSrc, $fullDest);
      OS::execute($command);
      chmod($fullDest, 0644);
    }
  }

  // $src is a file anywhere on the disk, while $dest is a subpath of the static server
  static function copy($src, $dest) {
    $fullDest = Config::STATIC_PATH . $dest;
    self::mkdir(dirname($fullDest));
    copy($src, $fullDest);
    chmod($fullDest, 0644);
  }

  // similar to copy, but also deletes $src
  static function move($src, $dest) {
    self::copy($src, $dest);
    unlink($src);
  }

  static function putContents(&$contents, $dest) {
    $fullDest = Config::STATIC_PATH . $dest;
    self::mkdir(dirname($fullDest));
    file_put_contents($fullDest, $contents);
    chmod($fullDest, 0644);
  }

  static function delete($file) {
    @unlink(Config::STATIC_PATH . $file);
  }

}
