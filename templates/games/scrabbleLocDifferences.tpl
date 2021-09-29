{extends "layout-admin.tpl"}

{block "title"}Lista Oficială de Cuvinte{/block}

{block "content"}
  <h3>
    Diferențe între LOC {$versions.0} și LOC {$versions.1} ({$listType})
  </h3>

  <p>
    <a class="btn btn-outline-secondary" href="{$zipUrl}">
      {include "bits/icon.tpl" i=file_download}
      descarcă
    </a>
    <a class="btn btn-link" href="{Router::link('games/scrabble')}">
      {include "bits/icon.tpl" i=arrow_back}
      înapoi
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
