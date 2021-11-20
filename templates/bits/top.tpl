<table id="{$tableId}" class="table tablesorter {if $pager}ts-pager{/if}">
  <thead>
    <tr>
      <th>{t}rank{/t}</th>
      <th>{t}name{/t}</th>
      <th>{t}characters{/t}</th>
      <th>{t}definitions{/t}</th>
      <th>{t}most recent submission{/t}</th>
    </tr>
  </thead>

  {if $pager}
    {include "bits/pager.tpl" id="{$tableId}Pager" colspan="5"}
  {/if}

  <tbody>
    {foreach $data as $place => $te}
      <tr class="{cycle values="color1,color2"}">
        <td data-text="{$place}">{$place+1}</td>
        <td class="nick">
          <a href="{Router::link('user/view')}/{$te->nick|escape:"url"}">
            {$te->nick|escape}
          </a>
        </td>
        <td data-text="{$te->numChars}">
          {$te->numChars|nf}
        </td>
        <td data-text="{$te->numDefs}">
          {$te->numDefs|nf}
        </td>
        <td
          style="filter: brightness({$te->getBrightness()})"
          data-text="{$te->lastTimestamp}">
          {LocaleUtil::date($te->lastTimestamp)}
        </td>
      </tr>
    {/foreach}
  </tbody>
</table>
