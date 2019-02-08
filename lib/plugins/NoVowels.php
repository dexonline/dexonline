<?php

/**
 * Pretend we don't have money to display vowels. A little too close to home, but oh well.
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'NoVowels' => [
 *   ],
 * ];
 *
 **/

class NoVowels extends Plugin {

  public function __construct($cfg) {
  }

  function searchEntryId(&$definitions) {
    $this->stripVowels($definitions);
    $this->addAlert();
  }

  function searchInflected(&$definitions, $sourceId) {
    $this->stripVowels($definitions);
    $this->addAlert();
  }

  private function stripVowels(&$definitions) {
    foreach ($definitions as &$d) {
      $d->internalRep = preg_replace('/[aeiouăâîáéíóúắấ]/iu', '', $d->internalRep);
    }
  }

  private function addAlert() {
    Smart::assign([
    ]);
    $message = Smart::fetch('plugins/noVowels/noVowels.tpl');
    FlashMessage::add($message, 'warning');
  }

}
