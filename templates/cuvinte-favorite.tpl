{extends "layout.tpl"}

{block "title"}Cuvinte favorite{/block}

{block "content"}
  <h3>Lista cuvintelor favorite pentru {$sUser->nick} ({$sUser->name})</h3>

  <dl class="favoriteDefs">
    {if $results}
      {foreach $results as $i => $row}
        <dd class="favoriteDef" data-idx={$i}>
          {include "bits/definition.tpl" 
          showRemoveBookmark=1 
          showCourtesyLink=1
          showFlagTypo=1
          showHistory=1}
        </dd>
      {/foreach}
    {else}
      Nu aveți niciun cuvânt favorit.
    {/if}
  </dl>
{/block}
