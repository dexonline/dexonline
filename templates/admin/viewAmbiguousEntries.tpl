{extends "layout-admin.tpl"}

{block "title"}Intrări ambigue{/block}

{block "content"}

  <h3>{$entries|count} intrări ambigue (cu descrieri identice)</h3>
  
  {foreach $entries as $e name=entryLoop}
    {include "bits/entry.tpl" entry=$e editLink=true}
    {if !$smarty.foreach.entryLoop.last} | {/if}
  {/foreach}

{/block}
