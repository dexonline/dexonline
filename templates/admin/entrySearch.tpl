{extends "layout-admin.tpl"}

{block "title"}Căutare intrări{/block}

{block "content"}

  <h3>
    {$count} rezultate
    {if $count > count($entries)}
      (maximum {$entries|count} afișate)
    {/if}
  </h3>

  {foreach $entries as $e name=entryLoop}
    {include "bits/entry.tpl" entry=$e editLink=true}
    {if !$smarty.foreach.entryLoop.last} | {/if}
  {/foreach}

{/block}
