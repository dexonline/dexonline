<?php

class SearchResult {

  const WOTD_IN_LIST = 0;     // definition itself is in WotD
  const WOTD_RELATED = 1;     // a related definition is in WotD
  const WOTD_NOT_IN_LIST = 2; // definition and its related definitions are not in WotD

  public $definition;
  public $user;
  public $sources;
  public $typos;
  public $bookmark;
  public $tags;
  public $wotdType;
  public $wotdDate;

  static function mapDefinitionArray($definitionArray) {
    if (empty($definitionArray)) {
      return [];
    }
    $results = [];
    $defIds = [];
    $sourceIds = [];
    $userIds = [];
    foreach ($definitionArray as $definition) {
      $defIds[] = $definition->id;
      $sourceIds[] = $definition->sourceId;
      $userIds[] = $definition->userId;
    }
    $userMap = Util::mapById(Model::factory('User')
                             ->where_in('id', array_unique($userIds))
                             ->find_many());
    $sourceMap = Util::mapById(Model::factory('Source')
                               ->where_in('id', array_unique($sourceIds))
                               ->find_many());
    foreach ($definitionArray as $definition) {
      $result = new SearchResult();
      $result->definition = $definition;
      $result->user = $userMap[$definition->userId];
      $result->sources = [ $sourceMap[$definition->sourceId] ];
      $result->typos = [];
      $result->wotdType = self::WOTD_NOT_IN_LIST;
      $result->wotdDate = null;
      $result->bookmark = false;
      $result->tags = Tag::loadByDefinitionId($definition->id);
      $results[$definition->id] = $result;
    }

    $typos = Model::factory('Typo')
           ->where_in('definitionId', $defIds)
           ->order_by_asc('id')
           ->find_many();
    foreach ($typos as $t) {
      $results[$t->definitionId]->typos[] = $t;
    }

    if ($suid = User::getActiveId()) {
      // This actually requires a stronger condition: that the user has User::PRIV_WOTD privileges;
      // but that check would require a DB hit. So we check that the user is logged in, which
      // is cheap. The admin permission is checked in the template.

      // Select definitions that were themselves WotD or definitions from the same entries as the
      // former.
      $wotdRecs = Model::factory('Definition')
        ->table_alias('d')
        ->select('d.id')
        ->select('w.definitionId')
        ->select('w.displayDate')
        ->join('EntryDefinition', ['d.id', '=', 'ed1.definitionId'], 'ed1')
        ->join('EntryDefinition', ['ed1.entryId', '=', 'ed2.entryId'], 'ed2')
        ->join('WordOfTheDay', ['w.definitionId', '=', 'ed2.definitionId'], 'w')
        ->where_in('d.id', $defIds)
        ->find_many();
      foreach ($wotdRecs as $w) {
        $results[$w->id]->wotdType = ($w->id == $w->definitionId)
                                   ? self::WOTD_IN_LIST
                                   : self::WOTD_RELATED;
        $results[$w->id]->wotdDate = ($w->displayDate == '0000-00-00') ? null : $w->displayDate;

      }

      $bookmarks = Model::factory('UserWordBookmark')
        ->where('userId', $suid)
        ->where_in('definitionId', $defIds)
        ->find_many();
      foreach ($bookmarks as $b) {
        $results[$b->definitionId]->bookmark = true;
      }
    }

    return $results;
  }

  // If the user chose to exclude unofficial definitions, filter them out.
  // If the user may not see hidden definitions, filter those out.
  // Returns information about changes made.
  static function filter(&$searchResults) {
    $unofficialHidden = null;
    $sourcesHidden = null;
    $excludeUnofficial = Session::userPrefers(Preferences::EXCLUDE_UNOFFICIAL);

    foreach ($searchResults as $i => &$sr) {
      if ($excludeUnofficial && ($sr->sources[0]->type == Source::TYPE_UNOFFICIAL)) {
        // hide unofficial definitions
        $unofficialHidden = true;
        unset($searchResults[$i]);
      } else if (!User::can(User::PRIV_VIEW_HIDDEN) &&
                 (($sr->sources[0]->type == Source::TYPE_HIDDEN) ||
                  ($sr->definition->status == Definition::ST_HIDDEN))) {
        // hide hidden definitions or definitions from hidden sources
        $sourcesHidden[$sr->sources[0]->id] = $sr->sources[0];
        unset($searchResults[$i]);
      }
    }

    return [ $unofficialHidden, $sourcesHidden ];
  }

  // Collapse identical definitions, enumerating their sources
  static function hideIdentical(&$searchResults) {
    // hash all the internalRep's and store them
    $hashMap = [];
    foreach ($searchResults as $sr) {
      $hashMap[$sr->definition->id] = md5($sr->definition->internalRep);
    }

    // build a map of hash code => list of results
    $map = [];
    foreach ($searchResults as $sr) {
      $hashCode = $hashMap[$sr->definition->id];
      $map[$hashCode][] = $sr;
    }

    // sort each list of results by displayOrder
    // mark for deletion the second and following elements of each list
    $toDelete = [];
    foreach ($map as $hashCode => &$srs) {
      usort($srs, function($a, $b) {
        return $a->sources[0]->displayOrder - $b->sources[0]->displayOrder;
      });
      for ($i = 1; $i < count($srs); $i++) {
        $toDelete[$srs[$i]->definition->id] = true;
        $srs[0]->sources[] = $srs[$i]->sources[0];
      }
    }

    foreach ($searchResults as $i => &$sr) {
      if (isset($toDelete[$sr->definition->id])) {
        unset($searchResults[$i]);
      }
    }
  }

}
