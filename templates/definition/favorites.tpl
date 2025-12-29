{extends "layout.tpl"}

{block "title"}{cap}{t}favorite words{/t}{/cap} ({$results|count}){/block}

{block "content"}
  <h3>
    {cap}{t}favorite words{/t}{/cap} ({$results|count})
  </h3>

  <dl data-none-text="{t}You have no favorite words.{/t}">
    {foreach $results as $row}
      <dd>
				<strong>{$row@iteration}.</strong>
        {include "bits/definition.tpl"
          readMore=true
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
