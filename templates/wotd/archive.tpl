<div class="card mb-4">
  <div class="card-header fs-5 text-center">
    {if $showPrev==1}
      <a
        class="float-start"
        href="#"
        onclick="loadAjaxContent('{Router::link('wotd/archive')}/{$prevMonth}', '#wotdArchive');">
        {include "bits/icon.tpl" i=chevron_left}
      </a>
    {/if}

    <span id="wotdDate">
      {cap}{$month}{/cap} {$year}
    </span>

    {if $showNext==1}
      <a
        class="float-end"
        href="#"
        onclick="loadAjaxContent('{Router::link('wotd/archive')}/{$nextMonth}', '#wotdArchive');">
        {include "bits/icon.tpl" i=chevron_right}
      </a>
    {else}
      &nbsp;
    {/if}
  </div>

  <table class="table table-bordered mb-0 wotdArchiveTable">
    <thead>
      <tr class="wotdDays">
        {foreach $dayNames as $name}
          <th>{$name}</th>
        {/foreach}
      </tr>
    </thead>
    <tbody>
      {foreach $words as $week}
        <tr>
          {foreach $week as $day}
            {if $day}
              <td class="activeMonth">
                <div class="wotdDoM">{$day.dayOfMonth}</div>
                <div class="wotd-link">
                  {if $day.visible}
                    <a href="{Router::link('wotd/view')}/{$day.wotd->getUrlDate()}">
                      {$day.def->lexicon}
                    </a>
                  {else}
                    &nbsp;
                  {/if}
                </div>
                <div class="thumb">
                  {if $day.wotd && $day.wotd->image && $day.visible}
                    <a href="{Router::link('wotd/view')}/{$day.wotd->getUrlDate()}">
                      <img src="{$day.wotd->getMediumThumbUrl()}"
                        alt="thumbnail {$day.def->lexicon}">
                    </a>
                  {/if}
                </div>
              </td>
            {else}
              <td>&nbsp;</td>
            {/if}
          {/foreach}
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
