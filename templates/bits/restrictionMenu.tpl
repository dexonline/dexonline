{foreach $searchResults as $row}
  <div class="checkbox-row">
    <label class="btn btn-xs btn-default">
      <input
        class="objCheckbox bulk-checkbox"
        type="checkbox"
        name="selectedConstraints[]"
        value="{$row->code}"
        {if $row->code == $row->selected}checked{/if}>
      <i class="glyphicon glyphicon-ok"></i>
    </label>
    <b>{$row->code}</b> - {$row->description}
  </div>
{foreachelse}
  {include "bits/searchResultEmpty.tpl"}
{/foreach}
<div role="separator" class="divider"></div>
<div><button type="button" id="constraintAccept" class="btn btn-block btn-sm btn-primary">Accept</button></div>
