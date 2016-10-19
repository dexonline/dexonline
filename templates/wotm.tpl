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
  <h3>Cuvântul lunii {$timestamp|date_format:'%B %Y'}</h3>
  <div class="container panel panel-default">
    <div class="row panel-heading">

      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 wotm-navigation">
        {if isset($prevmon)}
          <a title="precedentul" href="{$wwwRoot}cuvantul-lunii/{$prevmon}"><span class="glyphicon glyphicon-chevron-left pull-left"></span></a>
        {/if}
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 wotm-navigation">
        {if isset($nextmon)}
          <a title="următorul" href="{$wwwRoot}cuvantul-lunii/{$nextmon}"><span class="glyphicon glyphicon-chevron-right pull-right"></span></a>
        {/if}
      </div>
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
