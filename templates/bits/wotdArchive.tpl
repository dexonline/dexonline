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
      <th>Luni</th>
      <th>Marți</th>
      <th>Miercuri</th>
      <th>Joi</th>
      <th>Vineri</th>
      <th>Sâmbătă</th>
      <th>Duminică</th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$words item=week}
      <tr>
        {foreach from=$week item=day}
          {if $day}
            <td class="activeMonth">
              <div class="wotdDoM">{$day.dayOfMonth}</div>
              <div>{if $day.visible}<a href="{$wwwRoot}cuvantul-zilei/{$day.wotd->displayDate|replace:'-':'/'}">{$day.def->lexicon}</a>{else}&nbsp;{/if}</div>
              <div class="thumb">
                {if $day.wotd && $day.wotd->image && $day.visible}
                  {strip}
                    <a href="{$wwwRoot}cuvantul-zilei/{$day.wotd->displayDate|replace:'-':'/'}">
                      <img src="{$day.wotd->getSmallThumbUrl()}"
                           alt="thumbnail {$day.def->lexicon}">
                    </a>
                  {/strip}
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
