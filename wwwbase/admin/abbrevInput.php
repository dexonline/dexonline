<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$sourceId = Request::get('source');
$saveButton = Request::has('saveButton');
$cancelButton = Request::has('cancelButton');
$delimiter = Request::get('delimiter');
$class = "success";
$message = "";

$userId = User::getActiveId();
$csv = Session::get('csv', NULL);

if ($_FILES && $_FILES["file"]) {
  if ($_FILES["file"]["tmp_name"] != '') {
    if ($_FILES["file"]["error"] > 0) {
      $class = "danger";
      switch ($_FILES["file"]["error"]) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          $errmess = "No file sent.";
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          $errmess = "Exceeded filesize limit.";
        default:
          $errmess = "Unknown errors.";
      }
      $message = "Eroare: " . $errmess;
    } else {
      //getting an array for csv file contents and counting lines without header
      $csv = csv_to_array($_FILES["file"]["tmp_name"], $delimiter ?: '|');

      // stashing the array for saving operation
      Session::set('csv', $csv);
    }
  }
}


if ($saveButton) {
  if (count($csv) > 0) { // precess the array only if we have some data
    $errCount = 0;

    foreach ($csv as $row) { // handle each row
      $ts = strtotime(date('Y-m-d H:i:s'));
      
      $abbrev = Model::factory('Abbreviation')->create();

      $abbrev->sourceId = $sourceId;
      $abbrev->enforced = $row['enforced'];
      $abbrev->ambiguous = $row['ambiguous'];
      $abbrev->caseSensitive = $row['caseSensitive'];
      $abbrev->short = trim($row['short']);
      $abbrev->long = trim($row['long']);
      $abbrev->createDate = $ts;
      $abbrev->modDate = $ts;
      $abbrev->modUserId = $userId;

      try {
        $abbrev->save();
      } catch (Exception $e) {
        $errCount++;
        $class = "danger";
        $message .= "<div> Eroare: " . $e->getMessage() . "</div>";
      }
    }

    $message .= count($csv) - $errCount . " abrevieri au fost introduse în baza de date" .
      ($class == "danger" ? ($errCount . " dintre ele au generat erori...") : "!");
  }
  $cancelButton = true;
  
} else {
  if ($_FILES && $_FILES["file"]) {
    $message .= "Fișierul " . $_FILES["file"]["name"] . " (" . count($csv) .
      " linii) a fost încărcat" .
      ($class == "danger" ? (" cu " . $errCount . " erori...") : "!");
  }
}

if ($cancelButton){
  Session::unsetVar('csv');
  $csv = array();
}

SmartyWrap::assign('csv', $csv);
SmartyWrap::assign('msgClass', $class);
SmartyWrap::assign('message', $message);
SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('allModeratorSources', Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());

SmartyWrap::addCss('admin');
SmartyWrap::display('admin/abbrevInput.tpl');

function csv_to_array($filename = '', $delimiter = '|') {
  if (!file_exists($filename) || !is_readable($filename)) {
    return array();
  }

  $header = NULL;
  $data = array();
  if (($handle = fopen($filename, 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
      if (!$header) {
        $header = $row;
      } else {
        $data[] = array_combine($header, $row);
      }
    }
    fclose($handle);
  }
  return $data;
}