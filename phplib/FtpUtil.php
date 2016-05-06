<?php

class FtpUtil {
  private $conn;

  function __construct() {
    $user = Config::get('static.user');
    $pass = Config::get('static.password');
    if ($user && $pass) {
      $this->conn = ftp_connect(Config::get('static.host'));
      ftp_login($this->conn, $user, $pass);
      ftp_pasv($this->conn, true);
    }
  }

  function __destruct() {
    if ($this->conn) {
      ftp_close($this->conn);
    }
  }

  function connected() {
    return $this->conn != null;
  }

  function staticServerPut($localFile, $remoteFile) {
    if ($this->conn) {
      // Create the directory recursively
      $parts = explode('/', dirname($remoteFile));
      $partial = '';
      foreach ($parts as $part) {
        $partial .= '/' . $part;
        @ftp_mkdir($this->conn, $partial);
      }

      ftp_put($this->conn, Config::get('static.path') . $remoteFile, $localFile, FTP_BINARY);
    }
  }

  function staticServerPutContents(&$contents, $remoteFile) {
    $tmpFile = tempnam(Config::get('global.tempDir'), 'ftp_');
    file_put_contents($tmpFile, $contents);
    $this->staticServerPut($tmpFile, $remoteFile);
    unlink($tmpFile);
  }

  function staticServerDelete($remoteFile) {
    @ftp_delete($this->conn, Config::get('static.path') . $remoteFile);
  }

  function staticServerFileExists($remoteFile) {
    $listing = @ftp_nlist($this->conn, Config::get('static.path') . $remoteFile);
    return !empty($listing);
  }

  function staticServerRelativeFileList($rel_path) {
    $path = sprintf("%s/%s", Config::get('static.path'), $rel_path);
    $file_list = ftp_nlist($this->conn, $path);
    return $file_list;
  }
}

?>
