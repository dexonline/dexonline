{extends "layout.tpl"}

{block "title"}
    Expresia zilei: {$title}
{/block}

{block "pageDescription"}
  <meta
    name="description"
    content="Expresia zilei la dexonline: {$searchResult->definition->lexicon}">
{/block}

{block "openGraph"}
    {* Nothing -- so crawlers index the image of the month instead. *}
{/block}

{block "content"}
  <h3>Expresia: {$title}</h3>
  <div class="card mb-3">
    <div class="card-header fs-2 px-2 py-0 d-flex justify-content-between">
        {if isset($prevmon)}
          <a title="{t}previous{/t}" href="{Router::link('wote/view')}/{$prevmon}">
              {include "bits/icon.tpl" i=chevron_left}
          </a>
        {/if}
        {if isset($nextmon)}
          <a title="{t}next{/t}" href="{Router::link('wote/view')}/{$nextmon}">
              {include "bits/icon.tpl" i=chevron_right}
          </a>
        {/if}
    </div>

    <div class="card-body pb-0">

        {if $imageUrl}
          <img
            class="img-fluid mx-auto d-block"
            src="{$imageXLUrl}"
            srcset="
            {$imageUrl} 0.5x,
            {$imageXXLUrl} 1.8x
            "
            alt="{$searchResult->definition->lexicon}"
            title="{$searchResult->definition->lexicon}">
          <div class="text-muted text-end">
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
        <div class="card-footer">
          <b>{t}Chosen because:{/t}</b> {$reason}
        </div>
      {/if}
  </div>
  <br/>

  <h3>Dicționarul vizual al expresiilor cu și despre mâncare</h3>
  <div class="card mb-4">
    <table class="table table-bordered mb-0 wotdArchiveTable img-center ">
      <tbody>
        {foreach $words as $week}
          <tr>
            {foreach $week as $day}
              {if $day}
                <td class="activeMonth">
                  <div class="thumb">
                    {if $day.wotd && $day.wotd->image && $day.visible}
                      <a href="{Router::link('wote/view')}/{$day.wotd->id}">
                        <img
                          src="{$day.wotd->getXMediumThumbUrl()}"
                          alt="thumbnail {$day.wotd->title}"
                          title="{$day.wotd->title}"
                        >
                      </a>
                    {/if}
                  </div>
                  <div class="wotd-link text-center">
                      {if $day.visible}
                        <a href="{Router::link('wote/view')}/{$day.wotd->id}">
                            {$day.wotd->title}
                        </a>
                      {/if}
                  </div>
                </td>
              {else}
                <td></td>
              {/if}
            {/foreach}
          </tr>
        {/foreach}
      </tbody>
    </table>
   </div>
{/block}
