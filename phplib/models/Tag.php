<?php

class Tag extends BaseObject implements DatedObject {
  public static $_table = 'Tag';

  const DEFAULT_COLOR = '#ffffff';
  const DEFAULT_BACKGROUND = '#1e83c2';

  // populated during loadTree()
  public $children = [];

  function getColor() {
    return $this->color ? $this->color : self::DEFAULT_COLOR;
  }

  function setColor($color) {
    $this->color = ($color == self::DEFAULT_COLOR) ? '' : $color;
  }

  function getBackground() {
    return $this->background ? $this->background : self::DEFAULT_BACKGROUND;
  }

  function setBackground($background) {
    $this->background = ($background == self::DEFAULT_BACKGROUND) ? '' : $background;
  }

  function getFrequentValues($field, $default) {
    $data = Model::factory('Tag')
          ->select($field)
          ->group_by($field)
          ->order_by_expr('count(*) desc')
          ->limit(10)
          ->find_many();

    $results = [];
    foreach ($data as $row) {
      $results[] = $row->$field ? $row->$field : $default;
    }
    return $results;
  }

  function getFrequent($type, $count) {
    return Model::factory('Tag')
      ->table_alias('t')
      ->select('t.*')
      ->join('ObjectTag', ['t.id', '=', 'ot.tagId'], 'ot')
      ->where('ot.objectType', $type)
      ->group_by('t.id')
      ->order_by_expr('count(*) desc')
      ->limit($count)
      ->find_many();
  }

  static function loadByObject($objectType, $objectId) {
    return Model::factory('Tag')
      ->select('Tag.*')
      ->join('ObjectTag', ['Tag.id', '=', 'tagId'])
      ->where('ObjectTag.objectType', $objectType)
      ->where('ObjectTag.objectId', $objectId)
      ->order_by_asc('ObjectTag.id')
      ->find_many();
  }

  static function loadByDefinitionId($defId) {
    return self::loadByObject(ObjectTag::TYPE_DEFINITION, $defId);
  }

  static function loadByMeaningId($meaningId) {
    return self::loadByObject(ObjectTag::TYPE_MEANING, $meaningId);
  }

  // Returns an array of root tags with their $children fields populated
  static function loadTree() {
    $tags = Model::factory('Tag')->order_by_asc('value')->find_many();

    // Map the tags by id
    $map = [];
    foreach ($tags as $t) {
      $map[$t->id] = $t;
    }

    // Make each tag its parent's child
    foreach ($tags as $t) {
      if ($t->parentId) {
        $p = $map[$t->parentId];
        $p->children[] = $t;
      }
    }

    // Return just the roots
    return array_filter($tags, function($t) {
      return !$t->parentId;
    });
  }

  function getAncestors() {
    $p = $this;
    $result = [];

    do {
      array_unshift($result, $p);
      $p = Tag::get_by_id($p->parentId);
    } while ($p);

    return $result;
  }

  /**
   * Validates a tag for correctness. Returns an array of { field => array of errors }.
   **/
  function validate() {
    $errors = [];

    if (!$this->value) {
      $errors['value'][] = 'Numele nu poate fi vid.';
    }

    // make sure the chosen parent is not also a descendat - no cycles allowed
    $p = $this;
    do {
      $p = Tag::get_by_id($p->parentId);
    } while ($p && ($p->id != ($this->id)));
    if ($p) {
      $errors['parentId'][] = 'Nu puteți selecta drept părinte un descendent al etichetei.';
    }

    return $errors;
  }

  function delete() {
    ObjectTag::delete_all_by_tagId($this->id);
    parent::delete();
    Log::warning("Deleted tag {$this->id} ({$this->value})");
  }

  function __toString() {
    return "[{$this->value}]";
  }
}

?>
