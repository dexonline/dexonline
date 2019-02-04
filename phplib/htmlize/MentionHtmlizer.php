<?php

/**
 * Helper class used to output various kinds of meaning mentions.
 **/

class MentionHtmlizer extends Htmlizer {
  // htmlize one instance of a meaning mention formatted as text[meaningID]
  function htmlize($match) {
    $text = $match[1];
    $meaningId = $match[2];
    $stars = strlen($match[3]);

    $m = Meaning::get_by_id($meaningId);
    $bc = $m ? $m->breadcrumb : '?';

    switch ($stars) {
      case 0: $contents = $text; break;
      case 1: $contents = "$text (<b>$bc</b>)"; break;
      case 2: $contents = "(<b>$bc</b>)"; break;
    }

    $attributes = sprintf(
      'data-toggle="popover" ' .
      'data-html="true" ' .
      'data-placement="auto right" ' .
      'class="mention" ' .
      'title="%s"', $meaningId);
    $result = sprintf('<span %s>%s</span>', $attributes, $contents);

    if ($m) {
      // Figure out the landing page that contains this meaning. Load an entry
      // associated with this meaning, preferably one that generates the form in $text.
      $statuses = [Entry::STRUCT_STATUS_DONE, Entry::STRUCT_STATUS_UNDER_REVIEW];
      $entry = Model::factory('Entry')
        ->select('e.*')
        ->table_alias('e')
        ->join('TreeEntry', ['e.id', '=', 'te.entryId'], 'te')
        ->join('Tree', ['te.treeId', '=', 't.id'], 't')
        ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
        ->join('InflectedForm', ['el.lexemeId', '=', 'i.lexemeId'], 'i')
        ->where('t.id', $m->treeId)
        ->where('t.status', Tree::ST_VISIBLE)
        ->where_in('e.structStatus', $statuses)
        ->order_by_expr(sprintf('i.formNoAccent = "%s" desc', addslashes($text)))
        ->find_one();

      if ($entry) {
        $href = sprintf('%sintrare/%s/%d/sinteza#meaning%d',
                        Config::URL_PREFIX,
                        $entry->getShortDescription(),
                        $entry->id,
                        $meaningId);
        $result = sprintf('<a href="%s" %s>%s</a>', $href, $attributes, $contents);
      }
    }

    return $result;
  }
}
