<tfoot>
  <tr id="{$id}">
    <th colspan="{$colspan}" class="text-center">
      <button type="button" class="btn first">
        {include "bits/icon.tpl" i=first_page}
      </button>
      <button type="button" class="btn prev">
        {include "bits/icon.tpl" i=navigate_before}
      </button>
      <span class="pagedisplay"></span>
      <button type="button" class="btn next">
        {include "bits/icon.tpl" i=navigate_next}
      </button>
      <button type="button" class="btn last">
        {include "bits/icon.tpl" i=last_page}
      </button>
      <select class="pagesize" title="alegeți mărimea paginii">
        <option value="15">15</option>
        <option value="30">30</option>
        <option value="50">50</option>
      </select>
    </th>
  </tr>
</tfoot>
