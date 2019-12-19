<?php
User::mustHave(User::PRIV_ADMIN);

define("MSG_SUCCESS" , "%d indecși au fost introduși în baza de date.");

/* the function is needed by processPage and csv_to_objects from UploadUtils */
function process_row($sourceId, $userId, $row) {
  $pageIndex = PageIndex::create(
    $sourceId,
    $row['volume'],
    $row['page'],
    trim($row['word']),
    $row['number'],
    $userId
  );

  return $pageIndex;
}

$csv = UploadUtils::processPage();

// create PageIndex objects so we can use the HtmlConverter and use them in smarty
Smart::assign([
  'indexes' => UploadUtils::csv_to_objects($csv, $sourceId, $userId),
  'modUser' => User::getActive(),
]);
Smart::addResources('admin');
Smart::display('pageIndex/upload.tpl');
