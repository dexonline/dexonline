<?php

class FtpUtil {

  static function staticServerPut($localFile, $remoteFile) {
    $conn = ftp_connect(Config::get('static.host'));
    ftp_login($conn, Config::get('static.user'), Config::get('static.password'));
    ftp_pasv($conn, true);
    @ftp_mkdir($conn, dirname($remoteFile));
    ftp_put($conn, $remoteFile, $localFile, FTP_BINARY);
  }

}

?>
