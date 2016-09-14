{extends "layout-admin.tpl"}

{block "title"}Intrări neasociate{/block}

{block "content"}

  <h3>{$entries|count} intrări neasociate cu definiții</h3>

  {include "admin/entryList.tpl"}

{/block}
