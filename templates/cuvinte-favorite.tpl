{extends "layout.tpl"}

{block "title"}{cap}{t}favorite words{/t}{/cap}{/block}

{block "content"}
  <h3>
    {cap}{t}favorite words{/t}{/cap}
  </h3>

  <dl
    class="favoriteDefs"
    data-none-text="{t}You have no favorite words.{/t}">
    {foreach $results as $i => $row}
      <dd>
        {include "bits/definition.tpl"
          showRemoveBookmark=1
          showCourtesyLink=1
          showFlagTypo=1
          showHistory=1}
      </dd>
    {foreachelse}
      {t}You have no favorite words.{/t}
    {/foreach}
  </dl>
{/block}
