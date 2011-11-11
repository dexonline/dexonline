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
      return array();
    }
    $results = array();
    $defIds = array();
    $sourceIds = array();
    $userIds = array();
    foreach($definitionArray as $definition) {
      $defIds[] = $definition->id;
      $sourceIds[] = $definition->sourceId;
      $userIds[] = $definition->userId;
    }
    $userMap = self::mapById(Model::factory('User')->where_in('id', array_unique($userIds))->find_many());
    $sourceMap = self::mapById(Model::factory('Source')->where_in('id', array_unique($sourceIds))->find_many());
    foreach($definitionArray as $definition) {
      $result = new SearchResult();
      $result->definition = $definition;
      $result->user = $userMap[$definition->userId];
      $result->source = $sourceMap[$definition->sourceId];
      $result->typos = array();
      $result->comment = null;
      $result->wotd = false;
      $result->bookmark = false;
      $results[$definition->id] = $result;
    }

    $typos = Model::factory('Typo')->where_in('definitionId', $defIds)->find_many();
    foreach ($typos as $t) {
      $results[$t->definitionId]->typos[] = $t;
    }

    $comments = Model::factory('Comment')->where_in('definitionId', $defIds)->where('status', ST_ACTIVE)->find_many();
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
        ->raw_query("select R.refId from WordOfTheDay W join WordOfTheDayRel R on W.id = R.wotdId " .
                    "where R.refId in ($defIdString) and refType = 'Definition'", null)
        ->find_many();
      foreach ($wotdStatuses as $w) {
        $results[$w->refId]->wotd = true;
      }

      $bookmarks = Model::factory('UserWordBookmark')->where('userId', $suid)->where_in('definitionId', $defIds)->find_many();
      foreach($bookmarks as $b) {
        $results[$b->definitionId]->bookmark = true;
      }
    }
    return $results;
  }

  private static function mapById($objects) {
    $result = array();
    foreach ($objects as $o) {
      $result[$o->id] = $o;
    }
    return $result;
  }
}

?>
