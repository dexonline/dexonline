<?php

class SimilarSource extends BaseObject {
  public static $_table = 'SimilarSource';

  /* Returns a Source object (or null if the given $sourceId doesn't have a similar source). */
  static function getSimilarSource($sourceId) {
    $ss = Model::factory('SimilarSource')->select('similarSource')->where('sourceId', $sourceId)->find_one();
    return $ss
      ? Source::get_by_id($ss->similarSource)
      : null;
  }

}
?>
