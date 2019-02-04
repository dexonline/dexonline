<?php

class HarmonizeTag extends BaseObject implements DatedObject {
  static $_table = 'HarmonizeTag';

  private $pending = null;

  function getTag() {
    return Tag::get_by_id($this->tagId);
  }

  static function getAll() {
    return Model::factory('HarmonizeTag')
      ->order_by_asc('modelType')
      ->order_by_expr('cast(modelNumber as unsigned)')
      ->find_many();
  }

  // returns a portion of a SQL clause, common to the select and insert clauses
  function getJoin() {
    $tagIds = Tag::getDescendantIds($this->tagId);

    $query = sprintf(
      'from Lexeme l ' .
      'left join ObjectTag ot ' .
      'on l.id = ot.objectId ' .
      'and ot.objectType = %d ' .
      'and ot.tagId in (%s) ' .
      'where ot.id is null ' .
      'and l.modelType = "%s" ',
      ObjectTag::TYPE_LEXEME,
      implode(',', $tagIds),
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
      $query = 'select count(*) ' . $this->getJoin();
      $this->pending = DB::getSingleValue($query);
    }
    return $this->pending;
  }

  // creates ObjectTag associations as needed. This is a low-level query for speed purposes.
  function apply() {
    $insert = sprintf(
      'insert into ObjectTag ' .
      '(objectId, objectType, tagId, createDate, modDate) ' .
      'select l.id, %d, %d, unix_timestamp(), unix_timestamp() ',
      ObjectTag::TYPE_LEXEME,
      $this->tagId);
    $query = $insert . $this->getJoin();
    DB::execute($query);
  }

  // Validates the rule. Sets flash errors if needed. Returns true on success.
  function validate() {
    if (!$this->tagId) {
      FlashMessage::add('Trebuie să alegeți o etichetă.');
    }

    return empty(FlashMessage::getMessages());
  }

}
