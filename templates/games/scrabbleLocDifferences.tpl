{extends "layout-admin.tpl"}

{block "title"}{t}Official Word List{/t}{/block}

{block "content"}
  <h3>
    {t 1=$version.0 2=$version.1 3=$listType}Differences between LOC %1 and LOC %2 (%3){/t}
  </h3>

  <p>
    <a class="btn btn-outline-secondary" href="{$zipUrl}">
      {include "bits/icon.tpl" i=file_download}
      {t}download{/t}
    </a>
    <a class="btn btn-link" href="{Router::link('games/scrabble')}">
      {include "bits/icon.tpl" i=arrow_back}
      {t}back{/t}
    </a>
  </p>

  {strip}
  <pre class="locDiff">
    {foreach $diff as $rec}
      <div class="{if $rec.0 == 'ins'}text-success{else}text-danger{/if}">
        {$rec.1}
      </div>
    {/foreach}
  </pre>
  {/strip}
{/block}
