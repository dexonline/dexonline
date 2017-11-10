<?php

$definitions = Model::factory('Definition')
->select('id')
->select('internalRep')
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
  }
}

