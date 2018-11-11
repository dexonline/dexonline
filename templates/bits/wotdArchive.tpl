<table class="table table-bordered table-striped wotdArchiveTable">
  <caption class="text-center table-caption">
    <div id="wotdArchiveHeader">
      {if $showPrev==1}
        <span onclick="loadAjaxContent('{$wwwRoot}{$prevMonth}', '#wotdArchive');" id="navLeft"
              class="glyphicon glyphicon-chevron-left pull-left"></span>
      {else}
        &nbsp
      {/if}

      <span id="wotdDate">{$month|capitalize} {$year}</span>

      {if $showNext==1}
        <span onclick="loadAjaxContent('{$wwwRoot}{$nextMonth}', '#wotdArchive');" id="navRight"
              class="glyphicon glyphicon-chevron-right pull-right"></span>
      {else}
        &nbsp;
      {/if}
    </div>
  </caption>
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
                  <a href="{$wwwRoot}cuvantul-zilei/{$day.wotd->getUrlDate()}">
                    {$day.def->lexicon}
                  </a>
                {else}
                  &nbsp;
                {/if}
              </div>
              <div class="thumb">
                {if $day.wotd && $day.wotd->image && $day.visible}
                  <a href="{$wwwRoot}cuvantul-zilei/{$day.wotd->getUrlDate()}">
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
