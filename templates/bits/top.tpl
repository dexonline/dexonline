<table id="{$tableId}">
  <thead>
    <tr>
      <th>{'Rank'|_}</th>
      <th>{'Name'|_}</th>
      <th>{'Characters'|_}</th>
      <th>{'Definitions'|_}</th>
      <th>{'Most recent submission'|_}</th>
    </tr>
  </thead>

  {if $pager}
    {include "bits/pager.tpl" id="{$tableId}Pager" colspan="5"}
  {/if}

  <tbody>
    {foreach $data as $place => $row}
      {math equation="max(255 - days, 0)" days=$row->days assign=color}
      <tr class="{cycle values="color1,color2"}">
        <td>{$place+1}</td>
        <td class="nick">
          <a href="utilizator/{$row->userNick|escape:"url"}">{$row->userNick|escape}</a>
        </td>
        <td data-text="{$row->numChars}">
          {Locale::number($row->numChars)}
        </td>
        <td data-text="{$row->numDefinitions}">
          {Locale::number($row->numDefinitions)}
        </td>
        <td
          style="color: {$color|string_format:"#%02x0000"}"
          data-text="{$row->timestamp}">
          {Locale::date($row->timestamp)}
        </td>
      </tr>
    {/foreach}
  </tbody>
</table>
