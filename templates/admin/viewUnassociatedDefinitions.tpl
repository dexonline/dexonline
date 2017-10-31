{extends "layout-admin.tpl"}

{block "title"}Definiții neasociate{/block}

{block "content"}

  <h3>{$searchResults|count} definiții neasociate</h3>

  {foreach $searchResults as $row}
    {include "bits/definition.tpl"
    showDate=1
    showDeleteLink=1
    showStatus=1}
  {/foreach}

{/block}
