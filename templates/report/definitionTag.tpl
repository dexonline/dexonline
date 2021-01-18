{extends "layout-admin.tpl"}

{block "title"}Definiții cu eticheta [{$tag->value}]{/block}

{block "content"}
  <h3>
    {$searchResults|count} definiții cu eticheta
    {include "bits/tag.tpl" t=$tag}    
  </h3>

  {foreach $searchResults as $row}
    {include "bits/definition.tpl"}
  {/foreach}

{/block}
