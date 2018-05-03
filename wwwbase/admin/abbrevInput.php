<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$sourceId = Request::get('source');
$saveButton = Request::has('saveButton');
$cancelButton = Request::has('cancelButton');
$delimiter = Request::get('delimiter') ?: '|';
$csvFile = Request::getFile('file');

$class = "success";
$message = "";
$errCount = 0;

$userId = User::getActiveId();
$csv = Session::get('csv', []);

try {
  if ($csvFile) {
    if ($csvFile["error"] !== UPLOAD_ERR_OK) {
      throw new UploadException($csvFile['error']);
    } else {
      if ($csvFile["tmp_name"] != '') {
        //getting an array for csv file contents
        $csv = csv_to_array($csvFile["tmp_name"], $delimiter);

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
      $abbrev = Abbreviation::create(
        $sourceId, trim($row['short']), trim($row['internalRep']), $row['ambiguous'],
        $row['caseSensitive'], $row['enforced'], $userId);

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
  $csv = [];
}

// create Abbreviation objects so we can use the HtmlConverter
$abbrevs = csv_to_objects($csv, $sourceId, $userId);

SmartyWrap::assign([
  'abbrevs' => $abbrevs,
  'msgClass' => $class,
  'message' => $message,
  'modUser' => User::getActive(),
]);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/abbrevInput.tpl');

/*************************************************************************/

function csv_to_array($filename = '', $delimiter = '|') {
  $data = [];

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

// converts an array of associative arrays to an array of Abbreviation objects.
function csv_to_objects($csv, $sourceId, $userId) {
  $results = [];
  foreach ($csv as $row) {
    $results[] = Abbreviation::create(
      $sourceId, $row['short'], $row['internalRep'], $row['ambiguous'],
      $row['caseSensitive'], $row['enforced'], $userId
    );
  }
  return $results;
}
