<?php

$definitions = Model::factory('Definition')
->select('id')
->select('internalRep')
->select('htmlRep')
->select('sourceId')
->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
->where_like('internalRep', '%|%|%|%')
->find_many();

foreach ($definitions as $d) {

  $links = AdminStringUtil::findRedundantLinks($d->internalRep);

  foreach ($links as $link) {
    print $link["original_word"] . ",";
    print $link["linked_lexem"] . ",";
    print $d->id . ",";
    print $link[short_reason] . ",";
    print "[definiție]" . "(https://dexonline.ro/definitie/" . $d->id . "),";
    print "[editează]" . "(https://dexonline.ro/admin/definitionEdit.php?definitionId=" . $d->id . ")\n";

    if ($link[short_reason] !== "nemodificat") {
      $d->internalRep = str_replace(
        "|" . $link["original_word"] . "|" . $link["linked_lexem"] . "|",
        $link["original_word"],
        $d->internalRep
      );

      $d->htmlRep = AdminStringUtil::htmlize($d->internalRep, $d->sourceId);
      $d->save();
    }
  }
}

