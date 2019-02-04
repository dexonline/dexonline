<?php

class TraineeSource extends BaseObject {
  public static $_table = 'TraineeSource';

  static function TraineeCanEditSource($userId, $sourceId) {
    $result = Model::factory('TraineeSource')
      ->where_equal('userId', $userId)
      ->where_equal('sourceId', $sourceId)
      ->count();

    return $result;
  }

}
