<?php

class FtpUtil {

  static function staticServerPut($localFile, $remoteFile) {
    $conn = ftp_connect(Config::get('static.host'));
    ftp_login($conn, Config::get('static.user'), Config::get('static.password'));
    ftp_pasv($conn, true);
    @ftp_mkdir($conn, dirname($remoteFile));
    ftp_put($conn, Config::get('static.path') . $remoteFile, $localFile, FTP_BINARY);
    ftp_close($conn);
  }

  static function staticServerPutContents(&$contents, $remoteFile) {
    $tmpFile = tempnam('/tmp', 'ftp_');
    file_put_contents($tmpFile, $contents);
    self::staticServerPut($tmpFile, $remoteFile);
    unlink($tmpFile);
  }

  static function staticServerDelete($remoteFile) {
    $conn = ftp_connect(Config::get('static.host'));
    ftp_login($conn, Config::get('static.user'), Config::get('static.password'));
    ftp_pasv($conn, true);
    @ftp_delete($conn, Config::get('static.path') . $remoteFile);
    ftp_close($conn);
  }

}

?>
