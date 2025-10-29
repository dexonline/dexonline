<?php

class Subtitle extends BaseObject {
  public static $_table = 'Subtitle';

  static function LoadSubtitles($word) {
    return Model::factory('Subtitle')
      ->join('VideoClip', 'VideoClip.id = Subtitle.clipId')
      ->select('VideoClip.videoId', 'id')
      ->select('Subtitle.start')
      ->whereEqual('Subtitle.word', $word)
      ->limit(20)
      ->find_array();
  }

}
