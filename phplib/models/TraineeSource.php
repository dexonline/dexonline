<?php

class TraineeSource extends BaseObject {
  public static $_table = 'TraineeSource';

  static function TraineeCanEditSource($userId, $sourceId) {
    $result = Model::factory('TraineeSource')
      ->where_equal('idUser', $userId)
      ->where_equal('idSource', $sourceId)
      ->count();

    return $result;
  }

}