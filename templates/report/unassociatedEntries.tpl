{extends "layout-admin.tpl"}

{block "title"}Intrări neasociate{/block}

{block "content"}

  <h3>{$entries|count} intrări neasociate cu definiții / lexeme</h3>

  {include "bits/adminEntryList.tpl"}

{/block}
