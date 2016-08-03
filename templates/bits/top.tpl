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
    <tfoot>
      <tr id="{$tableId}Pager">
        <th colspan="5" class="text-center">
          <button type="button" class="btn first">
            <i class="icon-step-backward glyphicon glyphicon-step-backward"></i>
          </button>
          <button type="button" class="btn prev">
            <i class="icon-arrow-left glyphicon glyphicon-backward"></i>
          </button>
          <span class="pagedisplay"></span>
          <button type="button" class="btn next">
            <i class="icon-arrow-right glyphicon glyphicon-forward"></i>
          </button>
          <button type="button" class="btn last">
            <i class="icon-step-forward glyphicon glyphicon-step-forward"></i>
          </button>
          <select class="pagesize input-mini" title="alegeți mărimea paginii">
            <option value="15">15</option>
            <option value="30">30</option>
            <option value="50">50</option>
          </select>
        </th>
      </tr>
    </tfoot>
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
