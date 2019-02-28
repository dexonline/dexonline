{extends "layout.tpl"}

{block "title"}
  {t}Word of the month for{/t} {$timestamp|date_format:'%B %Y'}:
  {$searchResult->definition->lexicon}
{/block}

{block "pageDescription"}
  <meta
    name="description"
    content="Cuvântul lunii {$timestamp|date_format:'%B %Y'} la dexonline: {$searchResult->definition->lexicon}">
{/block}

{block "openGraph"}
  {* Nothing -- so crawlers index the image of the month instead. *}
{/block}

{block "content"}
  <h3>{t}Word of the month for{/t} {$timestamp|date_format:'%B %Y'}</h3>
  <div class="container panel panel-default">
    <div class="row panel-heading">

      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 wotm-navigation">
        {if isset($prevmon)}
          <a title="{t}previous{/t}" href="{Router::link('wotm/view')}/{$prevmon}">
            <span class="glyphicon glyphicon-chevron-left pull-left"></span>
          </a>
        {/if}
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 wotm-navigation">
        {if isset($nextmon)}
          <a title="{t}next{/t}" href="{Router::link('wotm/view')}/{$nextmon}">
            <span class="glyphicon glyphicon-chevron-right pull-right"></span>
          </a>
        {/if}
      </div>
    </div>
    <div class="panel-body">

      {if $imageUrl}
        <img class="img-responsive center-block" src="{$imageUrl}" alt="{$searchResult->definition->lexicon}" title="{$searchResult->definition->lexicon}">
        <div class="text-muted pull-right">
          {$artist->credits|default:''}
        </div>
      {/if}

      {include "bits/definition.tpl"
        row=$searchResult
        showBookmark=1
        showCourtesyLink=1
        showFlagTypo=1
        showHistory=1}

    </div>
    {if $reason}
      <div class="row panel-footer">
        <b>{t}Chosen because:{/t}</b> {$reason}
      </div>
    {/if}
  </div>
{/block}
