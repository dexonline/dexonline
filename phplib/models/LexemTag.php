<?php

class LexemTag extends BaseObject implements DatedObject {
  public static $_table = 'LexemTag';

  public static function associate($lexemId, $tagId) {
    // The lexem and the tag should exist
    $lexem = Lexem::get_by_id($lexemId);
    $tag = Tag::get_by_id($tagId);
    if (!$lexem || !$tag) {
      return;
    }

    // The association itself should not exist.
    $lt = self::get_by_lexemId_tagId($lexemId, $tagId);
    if (!$lt) {
      $lt = Model::factory('LexemTag')->create();
      $lt->lexemId = $lexemId;
      $lt->tagId = $tagId;
      $lt->save();
    }
  }

}

?>
