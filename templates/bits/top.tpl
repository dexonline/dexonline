<table id="{$tableId}" class="tablesorter-blue">
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
        <td colspan="5" class="text-center">
          <ul class="list-inline">
            <li>
              <a href="#" class="first" title="prima pagină">
                <i class="glyphicon glyphicon-fast-backward"></i>
              </a>
            </li>
            <li>
              <a href="#" class="prev" title="pagina anterioară">
                <i class="glyphicon glyphicon-step-backward"></i>
              </a>
            </li>
            <li>
              <input type="text" class="pagedisplay" size="5"/>
            </li>
            <li>
              <a href="#" class="next" title="pagina următoare">
                <i class="glyphicon glyphicon-step-forward"></i>
              </a>
            </li>
            <li>
              <a href="#" class="last" title="ultima pagină">
                <i class="glyphicon glyphicon-fast-forward"></i>
              </a>
            </li>
            <li>
              <select class="pagesize form-control">
                <option value="15">15 pe pagină</option>
                <option value="30">30 pe pagină</option>
                <option value="50">50 pe pagină</option>
              </select>
            </li>
          </ul>
        </td>
      </tr>
    </tfoot>
  {/if}

  <tbody>
    {foreach from=$data item=row key=place}
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
