<table id="{$tableId}">
  <thead>
    <tr>
      <th>Loc</th>
      <th>Nume</th>
      <th>Nr. caractere</th>
      <th>Nr. definiții</th>
      <th>Data ultimei trimiteri</th>
    </tr>
  </thead>

  {if $pager}
    {include "bits/pager.tpl" id="{$tableId}Pager" colspan="5"}
  {/if}

  <tbody>
    {foreach $data as $place => $row}
      <tr class="{cycle values="color1,color2"}">
        <td>{$place+1}</td>
        <td class="nick"><a href="utilizator/{$row->userNick|escape:"url"}">{$row->userNick|escape}</a></td>
        <td data-text="{$row->numChars}">{$row->numChars|number_format:0:',':'.'}</td>
        <td data-text="{$row->numDefinitions}">{$row->numDefinitions|number_format:0:',':'.'}</td>
 
        {math equation="max(255 - days, 0)" days=$row->days assign=color}
        <td style="color: {$color|string_format:"#%02x0000"}" data-text="{$row->timestamp}">{$row->timestamp|date_format:"%d.%m.%Y"}</td>
      </tr>
    {/foreach}
  </tbody>
</table>
