<?php

/* A set of data about similar definitions from older dictionaries */
class SimilarRecord {
  public $source;      // null if there is no similar source
  public $definition;  // null if $source is null or if the definition doesn't have a similar definition in $source
  public $htmlDiff;    // null if $source or $definition are null
  public $identical;   // true iff $definition is not null and identical to the original definition

  static function create($definition, $entryIds) {
    // filter any $entryIds beginning with '@' (newly added entries)
    $entryIds = array_filter($entryIds, function($id) {
      return !Str::startsWith($id, '@');
    });

    $diffSize = 0;
    $sr = new SimilarRecord();
    $sr->source = SimilarSource::getSimilarSource($definition->sourceId);
    $sr->definition = $definition->loadSimilar($entryIds, $diffSize);

    if ($sr->definition) {
      $sr->htmlDiff = LDiff::htmlDiff($sr->definition->internalRep, $definition->internalRep, true);
    } else {
      $sr->htmlDiff = null;
    }
    $sr->identical = $sr->definition && ($diffSize == 0);
    return $sr;
  }

  /* Idiorm objects are not JSON-friendly unless you call as_array() on them */
  function getJsonFriendly() {
    return array(
      'source' => $this->source ? $this->source->as_array() : null,
      'definition' => $this->definition ? $this->definition->as_array() : null,
      'htmlDiff' => $this->htmlDiff,
      'identical' => $this->identical,
    );
  }

  function getJson() {
    return json_encode($this->getJsonFriendly());
  }
}

?>
