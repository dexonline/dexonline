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
    $defIdString = implode(',', $defIds);
    $userMap = db_getObjectsMapById(new User(), db_execute('select * from User where id in (' . implode(',', $userIds) . ')'));
    $sourceMap = db_getObjectsMapById(new Source(), db_execute('select * from Source where id in (' . implode(',', $sourceIds) . ')'));
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

    $typos = db_getObjects(new Typo(), db_execute("select * from Typo where definitionId in ($defIdString)"));
    foreach ($typos as $t) {
      $results[$t->definitionId]->typos[] = $t;
    }

    $comments = db_getObjects(new Comment(), db_execute("select * from Comment where definitionId in ($defIdString) and status = " . ST_ACTIVE));
    foreach ($comments as $c) {
      $results[$c->definitionId]->comment = $c;
      // We still run one query per comment author, but there are very few comments
      $results[$c->definitionId]->commentAuthor = User::get("id = {$c->userId}");
    }

    if ($suid = session_getUserId()) {
      // This actually requires a stronger condition: that the user has PRIV_WOTD privileges; but that check would require a DB hit.
      // So we check that the user is logged in, which is cheap. The admin permission is checked in the template.
      $wotdStatuses = db_getArray(db_execute("select R.refId from WordOfTheDay W join WordOfTheDayRel R on W.id = R.wotdId " .
                                             "where R.refId in ($defIdString) and refType = 'Definition'"));
      foreach ($wotdStatuses as $w) {
        $results[$w]->wotd = true;
      }

      $bookmarks = db_getArray(db_execute("select definitionId from UserWordBookmark where userId = $suid and definitionId in ($defIdString)"));
      foreach($bookmarks as $b) {
        $results[$b]->bookmark = true;
      }
    }
    return $results;
  }
}

?>
