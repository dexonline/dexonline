<table id="{$tableId}" class="minimalistTable tablesorter-blue">
  <thead>
    <tr>
      <th>Loc</th>
      <th>
         Nume
      </th>
      <th>
        Nr. caractere
      </th>
      <th>
        Nr. definiții
      </th>
      <th>
        Data ultimei trimiteri
      </th>
    </tr>
  </thead>

  {if $pager}
    <tfoot>
      <tr id="{$tableId}Pager">
        <td colspan="5">
          <img src="{$imgRoot}/icons/control_start.png" class="first" alt="Prima pagină"/>
          <img src="{$imgRoot}/icons/control_rewind.png" class="prev" alt="Pagina anterioară"/>
          <input type="text" class="pagedisplay" size="5"/>
          <img src="{$imgRoot}/icons/control_fastforward.png" class="next" alt="Pagina următoare"/>
          <img src="{$imgRoot}/icons/control_end.png" class="last" alt="Ultima pagină"/>
          <select class="pagesize">
            <option value="15">15 pe pagină</option>
            <option value="30">30 pe pagină</option>
            <option value="50">50 pe pagină</option>
          </select>
        </td>
      </tr>
    </tfoot>
  {/if}

  <tbody>
    {foreach from=$data item=row key=place}
      <tr class="{cycle values="color1,color2"}">
        <td>{$place+1}</td>
        <td class="nick"><a href="utilizator/{$row->userNick|escape:"url"}">{$row->userNick|escape}</a></td>
        <td class="numerical" data-text="{$row->numChars}">{$row->numChars|number_format:0:',':'.'}</td>
        <td class="numerical" data-text="{$row->numDefinitions}">{$row->numDefinitions|number_format:0:',':'.'}</td>
 
        {math equation="max(255 - days, 0)" days=$row->days assign=color}
        <td style="color: {$color|string_format:"#%02x0000"}" data-text="{$row->timestamp}">{$row->timestamp|date_format:"%d.%m.%Y"}</td>
      </tr>
    {/foreach}
  </tbody>
</table>

<script>
  $(document).ready(function() {
    $("#{$tableId}").tablesorter({
      sortInitialOrder: "desc"
    })
    {if $pager}.tablesorterPager({
      container: $("#{$tableId}Pager"),
      output: '{ldelim}page{rdelim}/{ldelim}totalPages{rdelim}',
      size: 15,
    })
    {/if}
    ;
  });
</script>
