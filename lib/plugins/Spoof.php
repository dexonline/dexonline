<?php

/**
 * Custom definitions. Results will be returned as if the corresponding word was searched for.
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'Spoof' => [
 *     'normalize' => true,
 *     'words' => [
 *       'rege' => 'regină',
 *     ],
 *   ],
 * ];
 *
 **/

class Spoof extends Plugin {

  /**
   * Will lowercase and remove diacritics from the search word so 'Mașină', 'mașină',
   * 'masină' and 'masina' will all match the same 'masina' defined here.
   * If set to false you will need to provide a mapping for each case. Default is true.
   **/
  private $normalize;

  /* words to spoof and corresponding words to actually search for */
  private $words;

  /* original and spoofed search queries */
  private $query;
  private $spoofedQuery;

  /* true if the spoofed query has diacritics or the user has a preference for them */
  private $hasDiacritics;

  public function __construct($cfg) {
    $this->normalize = $cfg['normalize'];
    $this->words = $cfg['words'];
  }

  function replaceSpoofedWord($s) {
    $pattern = '/^@(.*)(,?)@/U';
    $replacement = sprintf('@%s${2}@', mb_strtoupper($this->query));
    return preg_replace($pattern, $replacement, $s, 1);
  }

  function spoofDefinitions(&$definitions) {
    foreach ($definitions as $d) {
      $d->internalRep = $this->replaceSpoofedWord($d->internalRep);
    }
  }

  function searchStart($query, $hasDiacritics) {
    $normalized = $this->normalize
                ? mb_strtolower(Str::unicodeToLatin($query))
                : $query;

    $this->query = $query;
    $this->spoofedQuery = @$this->words[$normalized];
    if ($this->spoofedQuery) {
      $this->hasDiacritics = $hasDiacritics || Str::hasDiacritics($this->spoofedQuery);
    }
  }

  function searchEntryId(&$definitions) {
    if ($this->spoofedQuery) {
      $entries = Entry::searchInflectedForms($this->spoofedQuery, $this->hasDiacritics);
      $definitions = Definition::searchEntry($entries[0]);
      $this->spoofDefinitions($definitions);
    }
  }

  function searchInflected(&$definitions, $sourceId) {
    if ($this->spoofedQuery) {
      $entries = Entry::searchInflectedForms($this->spoofedQuery, $this->hasDiacritics);
      $definitions = Definition::loadForEntries($entries, $sourceId, $this->spoofedQuery);
      $this->spoofDefinitions($definitions);
    }
  }
}
