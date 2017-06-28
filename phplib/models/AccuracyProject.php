<?php

class AccuracyProject extends BaseObject implements DatedObject {
  static $_table = 'AccuracyProject';

  const METHOD_NEWEST = 0;
  const METHOD_OLDEST = 1;
  const METHOD_RANDOM = 2;
  static $METHOD_NAMES = [
    self::METHOD_NEWEST => 'descrescător după dată',
    self::METHOD_OLDEST => 'crescător după dată',
    self::METHOD_RANDOM => 'ordine aleatorie',
  ];

  // Who has read access to this project?
  const VIS_PRIVATE = 0;  // owner only
  const VIS_ADMIN = 1;    // owner and User::PRIV_ADMIN's
  const VIS_EDITOR = 2;   // owner, User::PRIV_ADMIN's and the editor being evaluated
  const VIS_PUBLIC = 3;   // all User::PRIV_ADMIN's and User::PRIV_EDIT's
  static $VIS_NAMES = [
    self::VIS_PRIVATE => 'doar autorul proiectului',
    self::VIS_ADMIN => 'autorul proiectului și administratorii',
    self::VIS_EDITOR => 'autorul proiectului, administratorii și editorul evaluat',
    self::VIS_PUBLIC => 'toți administratorii și editorii',
  ];

  // Below this speed (chars/sec) we ignore a definition when computing editor speed.
  const SLOW_LIMIT = 0.1;

  private $source = null;
  private $owner = null;
  private $user = null;

  // accuracy data, computed on demand
  public $evalCount = 0;  // number of evaluated definitions
  public $evalLength = 0; // length of evaluated definitions
  public $errorCount = 0; // number of errors
  public $defCount = 0;   // number of available definitions
  public $accuracy = 0;   // fraction of correct characters expressed as percentage
  public $errorRate = 0;  // errors per thousand characters

  static function getMethodNames() {
    return self::$METHOD_NAMES;
  }

  function visibleTo($user) {
    if ($user->id == $this->ownerId) {
      return true;
    }
    switch ($this->visibility) {
      case self::VIS_PRIVATE:
        return false;
      case self::VIS_ADMIN:
        return $user->moderator & User::PRIV_ADMIN;
      case self::VIS_EDITOR:
        return ($user->moderator & User::PRIV_ADMIN) || ($user->id == $this->userId);
      case self::VIS_PUBLIC:
        return $user->moderator & (User::PRIV_ADMIN | User::PRIV_EDIT);
    }
  }

  function getSource() {
    if ($this->source === null) {
      $this->source = Source::get_by_id($this->sourceId);
    }
    return $this->source;
  }

  function getUser() {
    if ($this->user === null) {
      $this->user = User::get_by_id($this->userId);
    }
    return $this->user;
  }

  function getOwner() {
    if ($this->owner === null) {
      $this->owner = User::get_by_id($this->ownerId);
    }
    return $this->owner;
  }

  function hasStartDate() {
    return $this->startDate && ($this->startDate != '0000-00-00');
  }

  function hasEndDate() {
    return $this->endDate && ($this->endDate != '0000-00-00');
  }

  // Returns a ready-to-run idiorm query.
  // When $forceNewest is true, sorts definitions by newest regardless of $method.
  function getQuery($forceNewest = false) {
    $q = Model::factory('Definition')
       ->where_in('status', [ Definition::ST_ACTIVE, Definition::ST_HIDDEN ])
       ->where('userId', $this->userId);

    if ($this->sourceId) {
      $q = $q->where('sourceId', $this->sourceId);
    }

    if ($this->hasStartDate()) {
      $ts = strtotime($this->startDate);
      $q = $q->where_gte('createDate', $ts);
    }

    if ($this->hasEndDate()) {
      $ts = strtotime($this->endDate);
      $q = $q->where_lte('createDate', $ts);
    }

    $method = $forceNewest ? self::METHOD_NEWEST : $this->method;
    switch ($method) {
      case self::METHOD_NEWEST:
        $q = $q->order_by_desc('createDate');
        break;

      case self::METHOD_OLDEST:
        $q = $q->order_by_asc('createDate');
        break;

      case self::METHOD_RANDOM:
        $q = $q->order_by_expr('rand()');
        break;
    }

    return $q;
  }

  // Finds a definition covered by the project that wasn't already evaluated in the same project.
  function getDefinition() {
    $evaled = "select definitionId from AccuracyRecord where projectId = {$this->id}";
    $q = $this->getQuery()
       ->where_raw("id not in ({$evaled})");

    // handle the step parameter; does not apply to METHOD_RANDOM
    if (($this->step > 1) &&
        ($this->lastCreateDate) &&
        ($this->method != self::METHOD_RANDOM)) {
      if ($this->method == self::METHOD_NEWEST) {
        $q = $q->where_lt('createDate', $this->lastCreateDate);
      } else {
        $q = $q->where_gt('createDate', $this->lastCreateDate);
      }
      $q = $q->offset($this->step - 1);
    }

    return $q->find_one();
  }

  // Returns an array of (id, lexicon) for all evaluated definitions.
  function getDefinitionData() {
    $data = Model::factory('Definition')
          ->table_alias('d')
          ->select('d.id')
          ->select('d.lexicon')
          ->select('ar.errors')
          ->join('AccuracyRecord', [ 'd.id', '=', 'ar.definitionId'], 'ar')
          ->where('ar.projectId', $this->id)
          ->order_by_desc('ar.createDate')
          ->find_array();
    return $data;
  }

  // Returns accuracy results based on the definitions evaluated so far.
  function computeAccuracyData() {
    $this->evalLength = 0;
    $this->errorCount = 0;

    $data = Model::factory('Definition')
          ->table_alias('d')
          ->select_expr('char_length(d.internalRep)', 'len')
          ->select('ar.errors')
          ->join('AccuracyRecord', [ 'd.id', '=', 'ar.definitionId'], 'ar')
          ->where('ar.projectId', $this->id)
          ->find_many();
    foreach ($data as $row) {
      $this->evalLength += $row->len;
      $this->errorCount += $row->errors;
    }

    $this->evalCount = count($data);
    $this->defCount = $this->getQuery()->count();
    if ($this->evalLength) {
      $this->accuracy = (1 - $this->errorCount / $this->evalLength) * 100;
      $this->errorRate = $this->errorCount / $this->evalLength * 1000;
    }
  }

  // Recomputes the total definition length and time spend
  function recomputeSpeedData() {
    DB::setBuffering(false);

    $defs = $this->getQuery(true)->find_many();

    $prev = 0; // timestamp of the *next* definition in chronological order
    $this->totalLength = 0;
    $this->timeSpent = 0;
    $this->ignoredDefinitions = 0;
    foreach ($defs as $d) {
      $ignored = true;
      if ($prev) {
        $time = $prev - $d->createDate;
        if ($time) {
          $len = mb_strlen($d->internalRep);
          $speed = $len / $time;
          if ($speed > self::SLOW_LIMIT) {
            $this->totalLength += $len;
            $this->timeSpent += $time;
            $ignored = false;
          }
        }
      }
      if ($ignored) {
        $this->ignoredDefinitions++;
      }
      $prev = $d->createDate;
    }

    DB::setBuffering(true);
  }

  // returns the speed in characters / hour
  function getSpeed() {
    if ($this->timeSpent) {
      return $this->totalLength * 3600 / $this->timeSpent;
    } else {
      return 0;
    }
  }

  // Validates the project. Sets flash errors if needed. Returns true on success.
  function validate() {
    if (!$this->name) {
      FlashMessage::add('Numele nu poate fi vid.');
    }
    if (!$this->userId) {
      FlashMessage::add('Utilizatorul nu poate fi vid.');
    }
    if ($this->startDate && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->startDate)) {
      FlashMessage::add('Data de început trebuie să aibă formatul AAAA-LL-ZZ');
    }
    if ($this->endDate && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->endDate)) {
      FlashMessage::add('Data de sfârșit trebuie să aibă formatul AAAA-LL-ZZ');
    }
    if ($this->step < 1) {
      FlashMessage::add('Pasul trebuie să fie pozitiv.');
    }

    // Count the characters in all the applicable definitions
    $count = $this->getQuery()->count();
    if ($count <= 100) {
      FlashMessage::add("Criteriile alese returnează doar {$count} definiții. " .
                        "Relaxați-le pentru a obține minim 100 de definiții.");
    }

    return empty(FlashMessage::getMessages());
  }


  function __toString() {
    $result = "{$this->name} (";

    $user = User::get_by_id($this->userId);
    $result .= $user->nick;

    if ($this->sourceId) {
      $source = Source::get_by_id($this->sourceId);
      $result .= ", {$source->shortName}";
    }

    if ($this->hasStartDate()) {
      $result .= ", de la {$this->startDate}";
    }

    if ($this->hasEndDate()) {
      $result .= ", până la {$this->endDate}";
    }

    $result .= ")";

    return $result;
  }

  function delete() {
    AccuracyRecord::delete_all_by_projectId($this->id);
    parent::delete();
  }
}

?>
