{extends "layout-admin.tpl"}

{block "title"}Intrări cu definiții de structurat{/block}

{block "content"}

  <h3>{$entries|count} intrări structurate cu definiții nestructurate</h3>

  {include "bits/adminEntryList.tpl"}

{/block}
