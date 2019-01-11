{extends "layout-admin.tpl"}

{block "title"}Intrări fără lexeme principale{/block}

{block "content"}

  <h3>{$entries|count} intrări fără lexeme principale</h3>
  
  {include "bits/adminEntryList.tpl"}

{/block}
