<?php

class SimilarSource extends BaseObject {
  public static $_table = 'SimilarSource';

  public static function getSimilarSource($sourceId) {
    $ss = Model::factory('SimilarSource')->select('similarSource')->where('sourceId', $sourceId)->find_one();
    return $ss ? $ss->similarSource : null;
  }

}
?>
