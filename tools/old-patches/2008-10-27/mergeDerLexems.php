<?php
require_once("../../phplib/util.php"); 

$lexems = Lexem::loadAmbiguous();

foreach ($lexems as $l) {
  $homonyms = $l->loadHomonyms();
  if (count($homonyms) == 1) {
    $h = $homonyms[0];
    if ($h->form == $l->form && $h->modelType != 'T') {
      print "Merging {$l->id} {$l->form} ({$l->modelType}{$l->modelNumber}{$l->restriction}) " .
        "into {$h->id} {$h->form} ({$h->modelType}{$h->modelNumber}{$h->restriction})\n";

      $defs = Definition::loadByLexemId($l->id);
      foreach ($defs as $def) {
        LexemDefinitionMap::associate($h->id, $def->id);
      }
      $l->delete();
    }
  }
}

?>
