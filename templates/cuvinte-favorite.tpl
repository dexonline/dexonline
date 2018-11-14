{extends "layout.tpl"}

{block "title"}Cuvinte favorite{/block}

{block "content"}
  <h3>Lista cuvintelor favorite pentru {User::getActive()} ({User::getActive()->name})</h3>

  <dl
    class="favoriteDefs"
    data-none-text="{'You have no favorite words.'|_}">
    {foreach $results as $i => $row}
      <dd>
        {include "bits/definition.tpl"
          showRemoveBookmark=1
          showCourtesyLink=1
          showFlagTypo=1
          showHistory=1}
      </dd>
    {foreachelse}
      {'You have no favorite words.'|_}
    {/foreach}
  </dl>
{/block}
