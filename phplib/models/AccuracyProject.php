<?php

class AccuracyProject extends BaseObject implements DatedObject {
  static $_table = 'AccuracyProject';

  // Who has read access to this project?
  const VIS_PRIVATE = 0;  // owner only
  const VIS_ADMIN = 1;    // owner and User::PRIV_ADMIN's
  const VIS_EDITOR = 2;   // owner, User::PRIV_ADMIN's and the editor being reviewed
  const VIS_PUBLIC = 3;   // all User::PRIV_ADMIN's and User::PRIV_EDIT's
  static $VIS_NAMES = [
    self::VIS_PRIVATE => 'doar autorul proiectului',
    self::VIS_ADMIN => 'autorul proiectului și administratorii',
    self::VIS_EDITOR => 'autorul proiectului, administratorii și editorul evaluat',
    self::VIS_PUBLIC => 'toți administratorii și editorii',
  ];

  // used by runQuery()
  const FETCH_COUNT = 1;
  const FETCH_LENGTH = 2;
  const FETCH_DATA = 3;

  const SORT_RAND = 1;
  const SORT_CREATE_DATE_DESC = 2;

  // Below this speed (chars/sec) we ignore a definition when computing editor speed.
  const SLOW_LIMIT = 0.1;

  private $source = null;
  private $owner = null;
  private $user = null;

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
    return $this->startDate != '0000-00-00';
  }

  function hasEndDate() {
    return $this->endDate != '0000-00-00';
  }

  /**
   * Runs a PDO query specific to this project. We cannot work at Idiorm level
   * because it does not support buffered queries.
   *
   * Apparently the best way to count the number of rows with PDO is to issue
   * a count(*) query: http://php.net/manual/en/pdostatement.rowcount.php
   *
   * @param int $fetch what to return; see FETCH_* constants
   * @param int $sort sort order; see SORT_* constants
   **/
  function runQuery($fetch, $sort = null) {
    // collect clauses
    $clauses = [
      sprintf('(status in (%d, %d))', Definition::ST_ACTIVE, Definition::ST_HIDDEN),
      sprintf('(userId = %d)', $this->userId),
    ];

    if ($this->sourceId) {
      $clauses[] = sprintf('(sourceId = %d)', $this->sourceId);
    }

    if ($this->lexiconPrefix) {
      $clauses[] = sprintf('(lexicon like "%s%%")', addslashes($this->lexiconPrefix));
    }

    if ($this->hasStartDate()) {
      $ts = strtotime($this->startDate);
      $clauses[] = sprintf('(createDate >= %d)', $ts);
    }

    if ($this->hasEndDate()) {
      $ts = strtotime($this->endDate);
      $clauses[] = sprintf('(createDate <= %d)', $ts);
    }

    // assemble the query
    $clauseString = implode(' and ', $clauses);

    switch ($fetch) {
      case self::FETCH_COUNT: $select = 'count(*)'; break;
      case self::FETCH_LENGTH: $select = 'sum(char_length(internalRep))'; break;
      case self::FETCH_DATA: $select = '*'; break;
    }

    switch ($sort) {
      case self::SORT_RAND: $order = 'order by rand()'; break;
      case self::SORT_CREATE_DATE_DESC: $order = 'order by createDate desc'; break;
      default: $order = '';
    }

    $q = sprintf('select %s from Definition where %s %s', $select, $clauseString, $order);

    // run the query and return the result;
    return DB::execute($q, PDO::FETCH_ASSOC);
  }

  // returns the number of definitions covered by this project
  function getProjectDefinitions() {
    $result = $this->runQuery(self::FETCH_COUNT);
    return $result->fetchColumn();
  }

  // returns the number of characters covered by this project
  function getProjectLength() {
    $result = $this->runQuery(self::FETCH_LENGTH);
    return $result->fetchColumn();
  }

  // returns the number of definitions in our sample
  function getSampleDefinitions() {
    return Model::factory('AccuracyRecord')
      ->where('projectId', $this->id)
      ->count();
  }

  // returns the number of characters in this sample, optionally filtered by the reviewed field
  function getSumLength($reviewed = null) {
    $q = Model::factory('Definition')
       ->table_alias('d')
       ->select_expr('sum(char_length(d.internalRep))', 'len')
       ->join('AccuracyRecord', ['d.id', '=', 'ar.definitionId'], 'ar')
       ->where('ar.projectId', $this->id);
    if ($reviewed !== null) {
      $q = $q->where('ar.reviewed', $reviewed);
    }

    return $q->find_one()->len;
  }

  // returns the number of characters in our sample
  function getSampleLength() {
    return $this->getSumLength();
  }

  // returns the number of reviewed definitions
  function getReviewedDefinitions() {
    return Model::factory('AccuracyRecord')
      ->where('projectId', $this->id)
      ->where('reviewed', true)
      ->count();
  }

  // returns the number of reviewed characters
  function getReviewedLength() {
    return $this->getSumLength(true);
  }

  function getErrorCount() {
    return Model::factory('AccuracyRecord')
      ->where('projectId', $this->id)
      ->sum('errors');
  }

  // $this->errorRate is (errors found) / (characters reviewed)
  // multiply by 1,000 to get the error rate per KB
  function getErrorsPerKb() {
    return $this->errorRate * 1000;
  }

  // another way of measuring the error rate
  function getAccuracy() {
    return 100 * (1 - $this->errorRate);
  }

  // returns the speed in characters / hour
  function getCharactersPerHour() {
    return $this->speed * 3600;
  }

  // Finds the alphabetically smallest definition covered by the project that
  // wasn't already reviewed.
  function getDefinition() {
    return Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('AccuracyRecord', ['ar.definitionId', '=', 'd.id'], 'ar')
      ->where('ar.projectId', $this->id)
      ->where('ar.reviewed', false)
      ->order_by_asc('d.lexicon')
      ->order_by_asc('d.createDate')
      ->find_one();
  }

  // Returns an array of (id, lexicon, errors) for all reviewed definitions.
  function getDefinitionData() {
    $data = Model::factory('Definition')
          ->table_alias('d')
          ->select('d.id')
          ->select('d.lexicon')
          ->select('ar.errors')
          ->join('AccuracyRecord', [ 'd.id', '=', 'ar.definitionId'], 'ar')
          ->where('ar.projectId', $this->id)
          ->where('ar.reviewed', true)
          ->order_by_desc('ar.modDate')
          ->find_array();
    return $data;
  }

  // Returns accuracy results based on the definitions reviewed so far.
  function computeAccuracyData() {
    $data = Model::factory('Definition')
          ->table_alias('d')
          ->select_expr('char_length(d.internalRep)', 'len')
          ->select('ar.errors')
          ->join('AccuracyRecord', [ 'd.id', '=', 'ar.definitionId'], 'ar')
          ->where('ar.projectId', $this->id)
          ->find_many();

    $errorCount = $this->getErrorCount();
    $rlen = $this->getReviewedLength();
    if ($rlen) {
      $this->errorRate = $errorCount / $rlen;
    }
  }

  // Recomputes $defCount, $totalLength and $speed
  function computeSpeedData() {
    DB::setBuffering(false);

    $this->defCount = $this->getProjectDefinitions();

    $defs = $this->runQuery(self::FETCH_DATA, self::SORT_CREATE_DATE_DESC);

    $prev = 0; // timestamp of the *next* definition in chronological order
    $this->totalLength = 0;
    $timeSpent = 0;
    foreach ($defs as $d) {
      if ($prev) {
        $time = $prev - $d['createDate'];
        if ($time) {
          $len = mb_strlen($d['internalRep']);
          $speed = $len / $time;
          if ($speed > self::SLOW_LIMIT) {
            $this->totalLength += $len;
            $timeSpent += $time;
          }
        }
      }
      $prev = $d['createDate'];
    }

    $this->speed = $timeSpent ? ($this->totalLength / $timeSpent) : 0;

    DB::setBuffering(true);
  }

  // select a random set of definitions totaling at least $length characters
  // and create AccuracyRecords for them
  function sampleDefinitions($length) {
    DB::setBuffering(false);
    $result = $this->runQuery(self::FETCH_DATA, self::SORT_RAND);

    // Save definition IDs in memory. We cannot run SQL queries while
    // buffering is off.
    $ids = [];
    while (($length > 0) && ($d = $result->fetch())) {
      $ids[] = $d['id'];
      $length -= mb_strlen($d['internalRep']);
    }

    $result->closeCursor(); // discard other rows

    DB::setBuffering(true);

    foreach ($ids as $id) {
      $ar = Model::factory('AccuracyRecord')->create();
      $ar->projectId = $this->id;
      $ar->definitionId = $id;
      $ar->save();
    }
  }

  // Validates the project. Sets flash errors if needed. Returns true on success.
  function validate($targetLength = 0) {
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

    // Ensure there are enough definitions for our $targetLength
    $len = $this->getProjectLength();
    if ($len < $targetLength) {
      FlashMessage::add(
        "Criteriile alese returnează definiții cu lungimea totală de {$len} caractere. " .
        "Relaxați-le pentru a obține lungimea dorită."
      );
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

    if ($this->lexiconPrefix) {
      $result .= ", {$this->lexiconPrefix}*";
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
