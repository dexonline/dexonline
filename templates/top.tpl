{extends "layout.tpl"}

{block "title"}Topul voluntarilor{/block}

{block "content"}
  <h3>Topul contribuțiilor manuale</h3>

  Pentru fiecare utilizator sunt indicate numărul de definiții trimise și numărul total
  de caractere din acele definiții. Comparativ, Biblia are circa 3.500.000 de caractere.

  {include "bits/top.tpl" data=$manualData tableId="manualTop" pager=1}

  <h3>Topul contribuțiilor automate</h3>

  În această categorie intră definiții introduse automat și dicționare puse la dispoziție
  în format digital.

  {include "bits/top.tpl" data=$bulkData tableId="bulkTop" pager=0}
{/block}
