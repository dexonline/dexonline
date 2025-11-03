<?php

class Subtitle extends BaseObject {
  public static $_table = 'Subtitle';

  const LIMIT_SUBS = 50;

  static function LoadSubtitles($word) {
    return Model::factory('Subtitle')
      ->join('VideoClip', 'VideoClip.id = Subtitle.clipId')
      ->select('VideoClip.videoId', 'id')
      ->select('Subtitle.start')
      ->whereEqual('Subtitle.word', $word)
      ->limit(Subtitle::LIMIT_SUBS)
      ->find_array();
  }

}
