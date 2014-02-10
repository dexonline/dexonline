<?php

class OCR extends BaseObject {
  public static $_table = 'OCR';

  public static function countAvailable($editorId) {
    return Model::factory('OCR')
      ->where('status', 'raw')
      ->where_raw(sprintf('(editorId is null or editorId = %d)', $editorId))
      ->count();
  }
}

?>
