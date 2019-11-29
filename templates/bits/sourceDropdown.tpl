<select name="{$sources.vars.name}"
  id="{$id}"
  class="form-control sourceDropdown"
  style="width: {$sources.vars.width}"
  {if $sources.vars.autosubmit}onchange="this.form.submit();"{/if}>
  {if !$sources.vars.skipAnySource}
    <option value="">{t}All dictionaries{/t}</option>
  {/if}
  {foreach $sources.resultSet as $source}
    <option value="{$source->$sources.vars.submitValue}" {if $sources.vars.selectedValue == $source->$sources.vars.submitValue}selected{/if}>
      {* All the select2-searchable text must go here, not in data-* attributes *}
      ({$source->shortName|escape})
      {$source->name|escape}
    </option>
  {/foreach}
</select>
