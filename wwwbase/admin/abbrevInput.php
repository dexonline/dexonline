<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$sourceId = Request::get('source');
$saveButton = Request::has('saveButton');
$cancelButton = Request::has('cancelButton');
$delimiter = Request::get('delimiter');
$csvFile = Request::getFile('file');

$class = "success";
$message = "";
$errCount = 0;

$userId = User::getActiveId();
$csv = Session::get('csv', NULL);

try {
  if ($csvFile) {
    if ($csvFile["error"] !== UPLOAD_ERR_OK) {
      throw new UploadException($csvFile['error']);
    } else {
      if ($csvFile["tmp_name"] != '') {
        //getting an array for csv file contents
        $csv = csv_to_array($csvFile["tmp_name"], $delimiter ?: '|');

        // stashing the array for saving operation
        Session::set('csv', $csv);
      }
    }
  }
} catch (Exception $e) {
  $class = "danger";
  $message .= $e->getMessage();
}

if ($saveButton) {
  if (count($csv) > 0) { // process the array only if we have some data
    

    foreach ($csv as $row) { // handle each row
      $abbrev = Model::factory('Abbreviation')->create();

      $abbrev->sourceId = $sourceId;
      $abbrev->enforced = $row['enforced'];
      $abbrev->ambiguous = $row['ambiguous'];
      $abbrev->caseSensitive = $row['caseSensitive'];
      $abbrev->short = trim($row['short']);
      $abbrev->internalRep = trim($row['internalRep']);
      $abbrev->htmlRep = Str::htmlize(trim($row['internalRep']), $sourceId);
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
      ($errCount ? (" :: " . $errCount . " dintre ele au generat erori...") : "!");
  }
  $cancelButton = true;
} else {
  if ($csvFile["name"] != '' && $class != "danger") {
    $message .= "Fișierul " . $csvFile["name"] . " (" . count($csv) .
      " linii) a fost încărcat" .
      ($errCount ? (" cu " . $errCount . " erori...") : "!");
  }
}

if ($cancelButton) {
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
  $data = array();

  if (file_exists($filename) && is_readable($filename)) {
    $header = NULL;
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
  }
  return $data;
}
