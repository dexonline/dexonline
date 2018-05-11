<?php

class HarmonizeModel extends BaseObject implements DatedObject {
  static $_table = 'HarmonizeModel';

  private $pending = null;

  function getTag() {
    return Tag::get_by_id($this->tagId);
  }

  static function getAll() {
    return Model::factory('HarmonizeModel')
      ->order_by_asc('modelType')
      ->order_by_expr('cast(modelNumber as unsigned)')
      ->find_many();
  }

  // returns a portion of a SQL clause, common to the select and insert clauses
  function getJoin() {
    $query = sprintf(
      'Lexeme l ' .
      'join ObjectTag ot ' .
      'on ot.objectId = l.id ' .
      'and ot.objectType = %d ' .
      'and ot.tagId = %d ' .
      'and l.modelType = "%s" ',
      ObjectTag::TYPE_LEXEME,
      $this->tagId,
      $this->modelType
    );
    if ($this->modelNumber) {
      $query .= sprintf(' and l.modelNumber = "%s"', $this->modelNumber);
    }
    return $query;
  }

  // counts lexemes to which the rule still needs to be applied
  function countPending() {
    if ($this->pending === null) {
      $query = 'select count(*) from ' . $this->getJoin();
      $this->pending = DB::getSingleValue($query);
    }
    return $this->pending;
  }

  // creates ObjectTag associations as needed. This is a low-level query for speed purposes.
  function apply() {
    $query = sprintf(
      'update %s set modelType = "%s", l.modDate = unix_timestamp()',
      $this->getJoin(),
      $this->newModelType);
    if ($this->newModelNumber) {
      $query .= sprintf(', modelNumber = "%s"', $this->newModelNumber);
    }
    DB::execute($query);
  }

  // Validates the rule. Sets flash errors if needed. Returns true on success.
  function validate() {
    if (!$this->tagId) {
      FlashMessage::add('Trebuie să alegeți o etichetă.');
    }

    if (($this->modelType == $this->newModelType) &&
        ($this->modelNumber == $this->newModelNumber)) {
      FlashMessage::add('Modelul nu poate fi același.');
    }

    $can1 = ModelType::canonicalize($this->modelType);
    $can2 = ModelType::canonicalize($this->newModelType);
    if ($can1 != $can2) {
      FlashMessage::add(
        'Puteți schimba modelul doar cu altul din aceeași grupă canonică (de ex. F ↔ IL.');
    }

    if (!$this->modelNumber && $this->newModelNumber) {
      FlashMessage::add("Nu puteți schimba numărul de model din (oricare) în {$this->newModelNumber}");
    }

    return empty(FlashMessage::getMessages());
  }

}
