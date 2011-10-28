<?php

class WikiKeyword extends BaseObject {
  public static function deleteByWikiArticleId($wikiArticleId) {
    $wks = db_find(new WikiKeyword(), "wikiArticleId = {$wikiArticleId}");
    foreach ($wks as $wk) {
      $wk->delete();
    }
  }
}

?>
