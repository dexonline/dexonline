<?php

/**
 * Field meanings:
 *   - bool hidden: include / exclude hidden definitions
 *   - bool manual: count definitions submitted manually / in bulk
 *   - bool lastYear: count definitions from last year / from all time
 */

class TopEntry extends BaseObject {
  public static $_table = 'TopEntry';

  const SORT_CHARS = 1;
  const SORT_DEFS = 2;

  const FIELD = [
    self::SORT_CHARS => 'numChars',
    self::SORT_DEFS => 'numDefs',
  ];

  /**
   * Returns a number between 1.0 (for definitions sent today) and 0.4 (for
   * definitions sent at least 730 days ago).
   */
  function getBrightness() {
    $days = (time() - $this->lastTimestamp) / 86400;
    $brightness = max(1.0 - 0.6 * ($days / 730), 0.4);
    return number_format($brightness, 2); // force decimal dot, not comma
  }

  function getRank(int $sort) {
    $field = self::FIELD[$sort];
    $hidden = (bool)User::can(User::PRIV_VIEW_HIDDEN);

    return 1 + Model::factory('TopEntry')
      ->where('hidden', $hidden)
      ->where('manual', true)
      ->where('lastYear', false)
      ->where_gt($field, $this->$field)
      ->count();
  }

  /**
   * Get a user's record in the manual, all-time top.
   */
  static function getForUser(int $userId) {
    $hidden = (bool)User::can(User::PRIV_VIEW_HIDDEN);

    return TopEntry::get_by_userId_hidden_manual_lastYear(
      $userId, $hidden, true, false);
  }

  /**
   * Returns an array of user stats.
   *
   * @param int $sort Sort by SORT_CHARS / SORT_DEFS
   * @param bool $manual Count manual / bulk contributions.
   * @param bool $lastYear Count contributions from last year / from all time.
   * @param bool $hidden Include/exclude hidden definitions.
   *   Defaults to the viewing user's privilege.
   */
  static function getTopData(int $sort, bool $manual, bool $lastYear, bool $hidden = null) {
    $field = self::FIELD[$sort];

    if ($hidden === null) {
      $hidden = (bool)User::can(User::PRIV_VIEW_HIDDEN);
    }

    return Model::factory('TopEntry')
      ->table_alias('te')
      ->select('te.*')
      ->select('u.nick')
      ->join('User', [ 'u.id', '=', 'te.userId'], 'u')
      ->where('te.hidden', $hidden)
      ->where('te.manual', $manual)
      ->where('te.lastYear', $lastYear)
      ->order_by_desc($field)
      ->order_by_asc('u.nick')
      ->find_many();
  }

}
