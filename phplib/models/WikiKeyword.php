<?php

class WikiKeyword extends BaseObject {
  public static $_table = 'WikiKeyword';

  public static function deleteByWikiArticleId($wikiArticleId) {
    $wks = WikiKeyword::get_all_by_wikiArticleId($wikiArticleId);
    foreach ($wks as $wk) {
      $wk->delete();
    }
  }
}

?>
