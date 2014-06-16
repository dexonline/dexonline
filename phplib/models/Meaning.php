<?php

class Meaning extends BaseObject implements DatedObject {
  public static $_table = 'Meaning';

  static function loadTree($lexemId) {
    $meanings = Model::factory('Meaning')->where('lexemId', $lexemId)->order_by_asc('displayOrder')->find_many();

    // Map the meanings by id
    $map = array();
    foreach ($meanings as $m) {
      $map[$m->id] = $m;
    }

    // Collect each node's children
    $children = array();
    foreach ($meanings as $m) {
      $children[$m->id] = array();
    }
    foreach ($meanings as $m) {
      if ($m->parentId) {
        $children[$m->parentId][$m->displayOrder] = $m->id;
      }
    }

    // Build a tree from every root
    $results = array();
    foreach ($meanings as $m) {
      if (!$m->parentId) {
        $results[] = self::buildTree($map, $m->id, $children);
      }
    }
    return $results;
  }

  /**
   * Returns a dictionary containing:
   * 'meaning': a Meaning object
   * 'sources', 'tags', 'synonyms', 'antonyms': collections of objects related to the meaning
   * 'children': a recursive dictionary containing this meaning's children
   **/
  private static function buildTree(&$map, $meaningId, &$children) {
    $results = array('meaning' => $map[$meaningId],
                     'sources' => MeaningSource::loadSourcesByMeaningId($meaningId),
                     'tags' => MeaningTag::loadByMeaningId($meaningId),
                     'synonyms' => Synonym::loadByMeaningId($meaningId, Synonym::TYPE_SYNONYM),
                     'antonyms' => Synonym::loadByMeaningId($meaningId, Synonym::TYPE_ANTONYM),
                     'children' => array());
    foreach ($children[$meaningId] as $childId) {
      $results['children'][] = self::buildTree($map, $childId, $children);
    }
    return $results;
  }

  /**
   * Convert a tree produced by the tree editor to the format used by loadTree.
   * We need this in case validation fails and we cannot save the tree, so we need to display it again.
   **/
  static function convertTree($meanings) {
    $meaningStack = array();
    $results = array();
    foreach ($meanings as $tuple) {
      $row = array();
      $m = $tuple->id ? self::get_by_id($tuple->id) : Model::factory('Meaning')->create();
      $m->internalRep = $tuple->internalRep;
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
      $m->internalComment = $tuple->internalComment;
      $m->htmlComment = AdminStringUtil::htmlize($m->internalComment, 0);
      $row['meaning'] = $m;

      $row['sources'] = Source::loadByIds(StringUtil::explode(',', $tuple->sourceIds));
      $row['tags'] = MeaningTag::loadByIds(StringUtil::explode(',', $tuple->meaningTagIds));
      $row['synonyms'] = Lexem::loadByIds(StringUtil::explode(',', $tuple->synonymIds));
      $row['antonyms'] = Lexem::loadByIds(StringUtil::explode(',', $tuple->antonymIds));
      $row['children'] = array();

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

  /* Save a tree produced by the tree editor in admin/lexemEdit.php */
  static function saveTree($meanings, $lexem) {
    $seenMeaningIds = array();

    // Keep track of the previous meaning ID at each level. This allows us to populate the parentId field
    $meaningStack = array();
    $displayOrder = 1;
    foreach ($meanings as $tuple) {
      $m = $tuple->id ? self::get_by_id($tuple->id) : Model::factory('Meaning')->create();
      $m->parentId = $tuple->level ? $meaningStack[$tuple->level - 1] : 0;
      $m->displayOrder = $displayOrder++;
      $m->breadcrumb = $tuple->breadcrumb;
      $m->userId = session_getUserId();
      $m->lexemId = $lexem->id;
      $m->internalRep = $tuple->internalRep;
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
      $m->internalComment = $tuple->internalComment;
      $m->htmlComment = AdminStringUtil::htmlize($m->internalComment, 0);
      $m->save();
      $meaningStack[$tuple->level] = $m->id;

      $sourceIds = StringUtil::explode(',', $tuple->sourceIds);
      MeaningSource::updateList(array('meaningId' => $m->id), 'sourceId', $sourceIds);
      $meaningTagIds = StringUtil::explode(',', $tuple->meaningTagIds);
      MeaningTagMap::updateList(array('meaningId' => $m->id), 'meaningTagId', $meaningTagIds);
      $synonymIds = StringUtil::explode(',', $tuple->synonymIds);
      Synonym::updateList(array('meaningId' => $m->id, 'type' => Synonym::TYPE_SYNONYM), 'lexemId', $synonymIds);
      $antonymIds = StringUtil::explode(',', $tuple->antonymIds);
      Synonym::updateList(array('meaningId' => $m->id, 'type' => Synonym::TYPE_ANTONYM), 'lexemId', $antonymIds);
      $seenMeaningIds[] = $m->id;
    }
    self::deleteNotInSet($seenMeaningIds, $lexem->id);
  }

  /* Deletes all the meanings associated $lexemId that aren't in the $meaningIds set */
  public static function deleteNotInSet($meaningIds, $lexemId) {
    $meanings = self::get_all_by_lexemId($lexemId);
    foreach ($meanings as $m) {
      if (!in_array($m->id, $meaningIds)) {
        $m->delete();
      }
    }
  }

  public function delete() {
    MeaningSource::deleteByMeaningId($this->id);
    MeaningTagMap::deleteByMeaningId($this->id);
    Synonym::delete_all_by_meaningId($this->id);
    parent::delete();
  }

  /**
   * Different from __clone(). We save the object to the database to assign it an ID. We also clone its descendants,
   * synonyms/antonyms, sources and tags.
   **/
  public function cloneMeaning($newLexemId, $newParentId) {
    $clone = $this->parisClone();
    $clone->lexemId = $newLexemId;
    $clone->parentId = $newParentId;
    $clone->save();

    // Clone its tags
    $mtms = MeaningTagMap::get_all_by_meaningId($this->id);
    foreach ($mtms as $mtm) {
      $mtmClone = $mtm->parisClone();
      $mtmClone->meaningId = $clone->id;
      $mtmClone->save();
    }

    // Clone its sources
    $mss = MeaningSource::get_all_by_meaningId($this->id);
    foreach ($mss as $ms) {
      $msClone = $ms->parisClone();
      $msClone->meaningId = $clone->id;
      $msClone->save();
    }

    // Clone its synonyms / antonyms
    $synonyms = Synonym::get_all_by_meaningId($this->id);
    foreach ($synonyms as $synonym) {
      $synonymClone = $synonym->parisClone();
      $synonymClone->meaningId = $clone->id;
      $synonymClone->save();
    }

    // Clone its children
    $children = Meaning::get_all_by_parentId($this->id);
    foreach ($children as $child) {
      $child->cloneMeaning($newLexemId, $clone->id);
    }
  }
}

?>
