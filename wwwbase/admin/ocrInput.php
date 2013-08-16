<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$sourceId = util_getRequestIntParameter('source');
$class = "msgOK";
$message = "";

if ($_FILES && $_FILES["file"]) {
  if ($_FILES["file"]["error"] > 0) {
    $class = "msgErr";
    $message =  "Eroare: " . $_FILES["file"]["error"];
  }
  else {
    $userId = session_getUserId();
    $ocrLot = Model::factory('OCRLot')->create();
    $ocrLot->userId = $userId;
    $ocrLot->sourceId = $sourceId;
    $ocrLot->fileName = $_FILES["file"]["name"];
    $ocrLot->fileSize = $_FILES["file"]["size"];
    $ocrLot->startedAt = date('Y-m-d H:i:s');

    try {
      $ocrLot->save();
    }
    catch (Exception $e) {
      $class = "msgErr";
      $message = "<div> Eroare: " . $e->getMessage() . "</div>";
    }

    if ($class != "msgErr") {
      $lotId = $ocrLot->id();
      $errCount = 0;
      $lineCount = 0;

      $fp = fopen($_FILES["file"]["tmp_name"],'r');
      while ($line = fgets($fp)) {
        $line = trim($line);
        if (!empty($line)) {
          $lineCount++;
          $ocr = Model::factory('OCR')->create();
          $ocr->lotId = $lotId;
          $ocr->userId = $userId;
          $ocr->sourceId = $sourceId;
          $ocr->ocrText = $line;
          $ocr->dateAdded = date('Y-m-d H:i:s');
          try {
            $ocr->save();
          }
          catch (Exception $e) {
            $errCount++;
            $class = "msgErr";
            $message .= "<div> Eroare: " . $e->getMessage() . "</div>";
          }
        }
      }

      $ocrLot->status = 'done';
      $ocrLot->save();
      $message .= "Fișierul " . $_FILES["file"]["name"] . " (" . $lineCount . " linii) a fost salvat" .  ($class == "msgErr" ? (" cu " . $errCount . " erori...") : "!");
    }

  }
}

SmartyWrap::assign("msgClass", $class);
SmartyWrap::assign("message", $message);
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::displayAdminPage('admin/ocrInput.ihtml');

?>
