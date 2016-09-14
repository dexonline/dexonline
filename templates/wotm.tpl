{extends "layout.tpl"}

{block "title"}
  Cuvântul lunii {$timestamp|date_format:'%B %Y'}: {$searchResult->definition->lexicon}
{/block}

{block "pageDescription"}
  <meta name="description"
        content="Cuvântul lunii {$timestamp|date_format:'%B %Y'} la dexonline: {$searchResult->definition->lexicon}"/>
{/block}

{block "openGraph"}
  {* Nothing -- so crawlers index the image of the month instead. *}
{/block}

{block "content"}
  <div class="panel panel-default">
    <div class="panel-heading">
      <span>Cuvântul lunii {$timestamp|date_format:'%B %Y'}</span>
      <span class="pull-right">
        {if isset($prevmon)}
          <a title="precedentul" href="{$wwwRoot}cuvantul-lunii/{$prevmon}"><span class="glyphicon glyphicon-chevron-left"></span></a>
        {/if}
        {if isset($nextmon)}
          <a title="următorul" href="{$wwwRoot}cuvantul-lunii/{$nextmon}"><span class="glyphicon glyphicon-chevron-right"></span></a>
        {/if}
      </span>
    </div>
    <div class="panel-body">
      {include "bits/definition.tpl" row=$searchResult}

      {if $imageUrl}
        <img class="img-responsive center-block" src="{$imageUrl}" alt="{$searchResult->definition->lexicon}" title="{$searchResult->definition->lexicon}"/>
        <div class="text-muted pull-right">
          {$artist->credits|default:''}
        </div>
      {/if}

    </div>
  </div>
{/block}
