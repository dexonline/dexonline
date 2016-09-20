<?php

class SearchResult {
  public $definition;
  public $user;
  public $source;
  public $typos;
  public $comment;
  public $commentAuthor = NULL;

  public static function mapDefinitionArray($definitionArray) {
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
    $userMap = self::mapById(Model::factory('User')->where_in('id', array_unique($userIds))->find_many());
    $sourceMap = self::mapById(Model::factory('Source')->where_in('id', array_unique($sourceIds))->find_many());
    foreach ($definitionArray as $definition) {
      $result = new SearchResult();
      $result->definition = $definition;
      $result->user = $userMap[$definition->userId];
      $result->source = $sourceMap[$definition->sourceId];
      $result->typos = [];
      $result->comment = null;
      $result->wotd = false;
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

    $comments = Model::factory('Comment')->where_in('definitionId', $defIds)->where('status', Definition::ST_ACTIVE)->find_many();
    foreach ($comments as $c) {
      $results[$c->definitionId]->comment = $c;
      // We still run one query per comment author, but there are very few comments
      $results[$c->definitionId]->commentAuthor = User::get_by_id($c->userId);
    }

    if ($suid = session_getUserId()) {
      $defIdString = implode(',', $defIds);

      // This actually requires a stronger condition: that the user has PRIV_WOTD privileges; but that check would require a DB hit.
      // So we check that the user is logged in, which is cheap. The admin permission is checked in the template.
      $wotdStatuses = ORM::for_table('WordOfTheDay')
        ->raw_query("select R.refId, W.displayDate from WordOfTheDay W join WordOfTheDayRel R on W.id = R.wotdId " .
                    "where R.refId in ($defIdString) and refType = 'Definition'")
        ->find_many();
      foreach ($wotdStatuses as $w) {
        $results[$w->refId]->wotd = $w->displayDate ? $w->displayDate : true;
      }

      $bookmarks = Model::factory('UserWordBookmark')->where('userId', $suid)->where_in('definitionId', $defIds)->find_many();
      foreach ($bookmarks as $b) {
        $results[$b->definitionId]->bookmark = true;
      }
    }
    return $results;
  }

  // For users who can see hidden definitions, does nothing.
  // For other users removes hidden search results from $searchResults and stores their sources in $sources
  public static function filterHidden(&$searchResults, &$sources) {
    if (!util_isModerator(PRIV_VIEW_HIDDEN)) {
      foreach ($searchResults as $i => &$sr) {
        if ($sr->source->type == Source::TYPE_HIDDEN ||
            $sr->definition->status == Definition::ST_HIDDEN) {
          $sources[$sr->source->id] = $sr->source;
          unset($searchResults[$i]);
        }
      }
    }
  }

  private static function mapById($objects) {
    $result = [];
    foreach ($objects as $o) {
      $result[$o->id] = $o;
    }
    return $result;
  }
}

?>
