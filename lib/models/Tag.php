<?php

class Tag extends BaseObject implements DatedObject {
  public static $_table = 'Tag';

  // keep these in sync with main-{light,dark}.scss
  const NUM_COLORS = 14;
  const DEFAULT_COLOR = 5;

  // populated during loadTree()
  public $children = [];

  function getCssStyle() {
    return sprintf(
      'style="background-color: var(--c-tag-bg-%d); color: var(--c-tag-%d);"',
      $this->color, $this->color);
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

  /**
   * Sample call: $meanings = $tag->loadObjects(
   *   'Meaning', ObjectTag::TYPE_MEANING, 20);
   */
  function loadObjects($class, $objectType, $limit) {
    return Model::factory($class)
      ->table_alias('c')
      ->select('c.*')
      ->join('ObjectTag', ['ot.objectId', '=', 'c.id'], 'ot')
      ->where('ot.objectType', $objectType)
      ->where('ot.tagId', $this->id)
      ->limit($limit)
      ->find_many();
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

  // returns the IDs of all ancestors of $tagId, including $tagId
  static function getAncestorIds($tagId) {
    $tag = Tag::get_by_id($tagId);
    $ancestors = $tag->getAncestors();
    $ids = Util::objectProperty($ancestors, 'id');
    return $ids;
  }

  // returns the IDs of all tags in the subtree of $tagId, including $tagId
  static function getDescendantIds($tagId) {
    $result = [ $tagId ];
    $ids = [ $tagId ];
    do {
      $tags = Model::factory('Tag')
        ->where_in('parentId', $ids)
        ->find_many();
      $ids = Util::objectProperty($tags, 'id');
      $result = array_merge($result, $ids);
    } while (count($ids));
    return $result;
  }

  function isDescendantOf($other) {
    $p = $this;
    while ($p && $p->id != $other->id) {
      $p = Tag::get_by_id($p->parentId);
    }
    return (bool)$p;
  }

  /**
   * Validates a tag for correctness. Returns an array of { field => array of errors }.
   **/
  function validate() {
    $errors = [];

    if (!$this->value) {
      $errors['value'][] = 'Numele nu poate fi vid.';
    }

    // make sure the chosen parent is not also a descendant - no cycles allowed
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
    HarmonizeTag::delete_all_by_tagId($this->id);
    HarmonizeModel::delete_all_by_tagId($this->id);
    parent::delete();
    Log::warning("Deleted tag {$this->id} ({$this->value})");
  }

  function __toString() {
    return "[{$this->value}]";
  }
}
