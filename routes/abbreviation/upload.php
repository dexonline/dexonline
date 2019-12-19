<?php
User::mustHave(User::PRIV_ADMIN);

define("MSG_SUCCESS" , "%d abrevieri au fost introduse în baza de date.");

/* the function is needed by processPage and csv_to_objects from UploadUtils */
function process_row($sourceId, $userId, $row) {
  $abbrev = Abbreviation::create(
    $sourceId,
    trim($row['short']),
    trim($row['internalRep']),
    $row['ambiguous'],
    $row['caseSensitive'],
    $row['enforced'],
    $userId
  );

  return $abbrev;
}

$csv = UploadUtils::processPage();

// create Abbreviation objects so we can use the HtmlConverter and use them in smarty
Smart::assign([
  'abbrevs' => UploadUtils::csv_to_objects($csv, $sourceId, $userId),
  'modUser' => User::getActive(),
]);
Smart::addResources('admin');
Smart::display('abbreviation/upload.tpl');
