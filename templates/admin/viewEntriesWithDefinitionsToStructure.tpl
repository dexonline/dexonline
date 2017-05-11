{extends "layout-admin.tpl"}

{block "title"}Intrări cu definiții de structurat{/block}

{block "content"}

  <h3>{$entries|count} intrări structurate cu definiții nestructurate</h3>
  
  {foreach $entries as $e name=entryLoop}
    {include "bits/entry.tpl" entry=$e editLink=true}
    {if !$smarty.foreach.entryLoop.last} | {/if}
  {/foreach}

{/block}
