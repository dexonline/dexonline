<?php

class WikiSection extends BaseObject {
  public static $_table = 'WikiSection';

  public static function truncate() {
      db_execute("truncate table WikiSection");
  }
}

?>
