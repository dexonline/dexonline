<?php

class FtpUtil {
  private $conn;

  function __construct() {
    $user = Config::FTP_USER;
    $pass = Config::FTP_PASSWORD;
    if ($user && $pass) {
      $this->conn = ftp_ssl_connect(Config::FTP_HOST);
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

  function staticServerGet($remoteFile, $localFile) {
    if ($this->conn) {
      ftp_get($this->conn, $localFile, Config::FTP_PATH . $remoteFile, FTP_BINARY);
    }
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

      ftp_put($this->conn, Config::FTP_PATH . $remoteFile, $localFile, FTP_BINARY);
    }
  }

  function staticServerPutContents(&$contents, $remoteFile) {
    $tmpFile = tempnam(Config::TEMP_DIR, 'ftp_');
    file_put_contents($tmpFile, $contents);
    $this->staticServerPut($tmpFile, $remoteFile);
    unlink($tmpFile);
  }

  function staticServerDelete($remoteFile) {
    @ftp_delete($this->conn, Config::FTP_PATH . $remoteFile);
  }

  function staticServerFileExists($remoteFile) {
    $listing = @ftp_nlist($this->conn, Config::FTP_PATH . $remoteFile);
    return !empty($listing);
  }

  function staticServerRelativeFileList($rel_path) {
    $path = sprintf("%s/%s", Config::FTP_PATH, $rel_path);
    $file_list = ftp_nlist($this->conn, $path);
    return $file_list;
  }
}
