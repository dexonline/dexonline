{extends "layout-admin.tpl"}

{block "title"}Definiții nemoderate{/block}

{block "content"}

  <h3>{$searchResults|count} definiții nemoderate</h3>

  {foreach $searchResults as $row}
    {include "bits/definition.tpl"
    showDate=1
    showDeleteLink=1
    showHistory=1}
  {/foreach}

{/block}
