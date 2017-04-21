<?php

class WikiSection extends BaseObject {
  public static $_table = 'WikiSection';

  static function truncate() {
      DB::execute("truncate table WikiSection");
  }
}

?>
