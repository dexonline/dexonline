{foreach $searchResults as $row}
  <div class="pull-right">
    <label class="btn btn-xs btn-default">
      <input class="objCheckbox bulk-checkbox" type="checkbox" name="selectedDefIds[]" value="{$row->definition->id}">
      <i class="glyphicon glyphicon-ok"></i>
    </label>
  </div>
  {include "bits/definition.tpl"}
{foreachelse}
  {include "bits/searchResultEmpty.tpl"}
{/foreach}
