<?php

class Meaning extends BaseObject implements DatedObject {
  public static $_table = 'Meaning';

  const TYPE_MEANING = 0;
  const TYPE_ETYMOLOGY = 1;
  const TYPE_EXAMPLE = 2;
  const TYPE_COMMENT = 3;

  public static $DISPLAY_NAMES = [
    self::TYPE_MEANING => '',
    self::TYPE_ETYMOLOGY => 'etimologie',
    self::TYPE_EXAMPLE => 'exemplu',
    self::TYPE_COMMENT => 'comentariu',
  ];

  public static $FIELD_NAMES = [
    self::TYPE_MEANING => 'sens',
    self::TYPE_ETYMOLOGY => 'etimologie',
    self::TYPE_EXAMPLE => 'exemplu',
    self::TYPE_COMMENT => 'comentariu (public)',
  ];

  private $tree = null;

  function getDisplayTypeName() {
    return self::$DISPLAY_NAMES[$this->type];
  }

  function getTree() {
    if ($this->tree === null) {
      $this->tree = Tree::get_by_id($this->treeId);
    }
    return $this->tree;
  }

  /**
   * Increases the first part of the breadcrumb by $x, so 3.5.1 increased by 7 becomes 10.5.1.
   **/
  function increaseBreadcrumb($x) {
    $parts = explode('.', $this->breadcrumb);
    $parts[0] += $x;
    $this->breadcrumb = implode('.', $parts);
  }

  /**
   * Fills in the displayOrder and breadcrumb fields for an array of meanings.
   * Assumes the parentId and type fields are filled in. The ID may be empty
   * (in which case that meaning should not have children).
   **/
  static function renumber($meanings) {
    $order = 0;
    $numChildren = [ 0 => 0 ];  // number of children seen so far for each meaningId
    $breadcrumb = [ 0 => '' ];  // breadcrumbs for meanings seen so far

    foreach ($meanings as $m) {
      $m->displayOrder = ++$order;

      if ($m->type == Meaning::TYPE_MEANING) {
        $m->breadcrumb = $breadcrumb[$m->parentId] . (++$numChildren[$m->parentId]) . '.';
      } else {
        $m->breadcrumb = '';
      }

      $breadcrumb[$m->id] = $m->breadcrumb;
      $numChildren[$m->id] = 0;
    }
  }

  /**
   * Convert a tree produced by the tree editor to the format used by loadTree.
   * We need this in case validation fails and we cannot save the tree, so we need to display it again.
   **/
  static function convertTree($meanings) {
    $meaningStack = [];
    $results = [];

    foreach ($meanings as $tuple) {
      $row = [];
      $m = $tuple->id
         ? self::get_by_id($tuple->id)
         : Model::factory('Meaning')->create();

      $m->type = $tuple->type;
      $m->internalRep = AdminStringUtil::sanitize($tuple->internalRep);
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);

      $row['meaning'] = $m;
      $row['sources'] = Source::loadByIds($tuple->sourceIds);
      $row['tags'] = Tag::loadByIds($tuple->tagIds);
      $row['relations'] = Relation::loadRelatedTrees($tuple->relationIds);
      $row['children'] = [];

      if ($tuple->level) {
        $meaningStack[$tuple->level - 1]['children'][] = &$row;
      } else {
        $results[] = &$row;
      }
      $meaningStack[$tuple->level] = &$row;
      unset($row);
    }

    return $results;
  }

  /* Save a tree produced by the tree editor in editTree.php */
  static function saveTree($meanings, $tree) {
    $seenMeaningIds = [];

    // Keep track of the previous meaning ID at each level. This allows us
    // to populate the parentId field
    $meaningStack = [];
    $displayOrder = 1;
    foreach ($meanings as $tuple) {
      $m = $tuple->id
         ? self::get_by_id($tuple->id)
         : Model::factory('Meaning')->create();
      $m->type = $tuple->type;
      $m->parentId = $tuple->level ? $meaningStack[$tuple->level - 1] : 0;
      $m->displayOrder = $displayOrder++;
      $m->breadcrumb = $tuple->breadcrumb;
      $m->userId = session_getUserId();
      $m->treeId = $tree->id;
      $m->internalRep = AdminStringUtil::sanitize($tuple->internalRep);
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
      $m->save();
      $meaningStack[$tuple->level] = $m->id;

      MeaningSource::updateList(['meaningId' => $m->id], 'sourceId', $tuple->sourceIds);
      ObjectTag::wipeAndRecreate($m->id, ObjectTag::TYPE_MEANING, $tuple->tagIds);
      foreach ($tuple->relationIds as $type => $treeIds) {
        if ($type) {
          Relation::updateList(['meaningId' => $m->id, 'type' => $type],
                               'treeId', $treeIds);
        }
      }
      $seenMeaningIds[] = $m->id;
    }
    self::deleteNotInSet($seenMeaningIds, $tree->id);
  }

  /* Deletes all the meanings associated with $treeId that aren't in the $meaningIds set */
  public static function deleteNotInSet($meaningIds, $treeId) {
    $meanings = self::get_all_by_treeId($treeId);
    foreach ($meanings as $m) {
      if (!in_array($m->id, $meaningIds)) {
        $m->delete();
      }
    }
  }

  public function save() {
    parent::save();

    // extract and save all mentions contained in this meaning

    preg_match_all("/\\[\\[(\d+)\\]\\]/", $this->internalRep, $m);
    $u = array_unique($m[1]);
    Mention::wipeAndRecreate($this->id, Mention::TYPE_TREE, $u);

    preg_match_all("/(?<!\\[)\\[(\d+)\\](?!\\])/", $this->internalRep, $m);
    $u = array_unique($m[1]);
    Mention::wipeAndRecreate($this->id, Mention::TYPE_MEANING, $u);
  }

  public function delete() {
    MeaningSource::delete_all_by_meaningId($this->id);
    ObjectTag::delete_all_by_objectId_objectType($this->id, ObjectTag::TYPE_MEANING);
    Relation::delete_all_by_meaningId($this->id);

    // Reprocess meanings mentioning this one to remove said mentions
    $mentions = Mention::getMeaningMentions($this->id);
    foreach ($mentions as $ment) {
      $m = Meaning::get_by_id($ment->meaningId);
      $m->internalRep = str_replace("[{$this->id}]", '', $m->internalRep);
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
      $m->save();
    }

    // Delete mentions containing this meaning on either side
    Mention::delete_all_by_meaningId($this->id);
    Mention::delete_all_by_objectId_objectType($this->id, Mention::TYPE_MEANING);
    parent::delete();
  }
}

?>
