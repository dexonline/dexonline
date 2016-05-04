<?php

class AccuracyProject extends BaseObject {
  static $_table = 'AccuracyProject';

  const METHOD_NEWEST = 0;
  const METHOD_OLDEST = 1;
  const METHOD_RANDOM = 2;
  const METHOD_NAMES = [
    self::METHOD_NEWEST => 'Descrescător după dată',
    self::METHOD_OLDEST => 'Crescător după dată',
    self::METHOD_RANDOM => 'Ordine aleatorie',
  ];

  static function getMethodNames() {
    return self::METHOD_NAMES;
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

    // Count the characters in all the applicable definitions
    $q = Model::factory('Definition')
       ->where_in('status', [ Definition::ST_ACTIVE, Definition::ST_HIDDEN ])
       ->where('userId', $this->userId);

    if ($this->sourceId) {
      $q = $q->where('sourceId', $this->sourceId);
    }
    if ($this->startDate) {
      $ts = strtotime($this->startDate);
      $q = $q->where_gte('createDate', $ts);
    }
    if ($this->endDate) {
      $ts = strtotime($this->endDate);
      $q = $q->where_lte('createDate', $ts);
    }

    $count = $q->count();
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

    if ($this->startDate != '0000-00-00') {
      $result .= ", de la {$this->startDate}";
    }

    if ($this->endDate != '0000-00-00') {
      $result .= ", până la {$this->endDate}";
    }

    $result .= ")";

    return $result;
  }
}

?>
