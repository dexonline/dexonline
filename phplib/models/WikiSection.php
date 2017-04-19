<?php

class WikiSection extends BaseObject {
  public static $_table = 'WikiSection';

  public static function truncate() {
      DB::execute("truncate table WikiSection");
  }
}

?>
