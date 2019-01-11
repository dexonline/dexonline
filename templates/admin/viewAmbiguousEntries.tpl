{extends "layout-admin.tpl"}

{block "title"}Intrări ambigue{/block}

{block "content"}

  <h3>{$entries|count} intrări ambigue (cu descrieri identice)</h3>

  {include "bits/adminEntryList.tpl"}

{/block}
