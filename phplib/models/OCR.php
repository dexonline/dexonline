<?php

class OCR extends BaseObject {
  public static $_table = 'OCR';

  public static function countAvailable($editorId) {
    return Model::factory('OCR')
      ->where('status', 'raw')
      ->where_raw(sprintf('(editorId is null or editorId = %d)', $editorId))
      ->count();
  }

  // Finds a record assigned to $editorId. Failing that, finds an unassigned record.
  public static function getNext($editorId) {
    $ocr = Model::factory('OCR')
         ->where('status', 'raw')
         ->where('editorId', $editorId)
         ->order_by_asc('dateModified')
         ->order_by_asc('id')
         ->find_one();
    if (!$ocr) {
      $ocr = Model::factory('OCR')
           ->where('status', 'raw')
           ->where_null('editorId')
           ->order_by_asc('dateModified')
           ->order_by_asc('id')
           ->find_one();
    }
    return $ocr;
  }
}

?>
