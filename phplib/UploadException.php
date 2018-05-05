<?php

class UploadException extends Exception {
  public function __construct($code, Exception $previous = null) {
    $msg = $this->codeToMessage($code);
    parent::__construct($msg, $code, $previous);
  }

  private function codeToMessage($code)
  {
    switch ($code) {
      case UPLOAD_ERR_INI_SIZE:
        $message = "Mărimea fișierului încărcat este mai mare decât valoarea directivei upload_max_filesize din php.ini";
        break;
      case UPLOAD_ERR_FORM_SIZE:
        $message = "Mărimea fișierului încărcat este mai mare decât valoarea directivei MAX_FILE_SIZE din formularul HTML";
        break;
      case UPLOAD_ERR_PARTIAL:
        $message = "Încărcare fișier întreruptă";
        break;
      case UPLOAD_ERR_NO_FILE:
        $message = "Nu s-a încărcat niciun fișier";
        break;
      case UPLOAD_ERR_NO_TMP_DIR:
        $message = "Lipsă director temporar";
        break;
      case UPLOAD_ERR_CANT_WRITE:
        $message = "Eroare la scrierea pe disc";
        break;
      case UPLOAD_ERR_EXTENSION:
        $message = "Extensie fișier neacceptată";
        break;

      default:
        $message = "Eroare necunoscută";
        break;
    }
    return $message;
  }
}
