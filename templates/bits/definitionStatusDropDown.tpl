<select name="{$statuses.vars.name}" class="form-control" {if $statuses.vars.disabled}disabled{/if}>
  {if $statuses.vars.anyOption}
    <option value="">oricare</option>
  {/if}
  {foreach $statuses.resultSet as $submitValue => $displayName}
    <option value="{$submitValue|escape}"
            {if (string)$submitValue == (string)$statuses.vars.selectedValue}selected{/if}>
      {$displayName|escape}
    </option>
  {/foreach}
</select>

{if $statuses.vars.disabled}
  <input type="hidden" name="{$statuses.vars.name}" value="{$statuses.vars.selectedValue}">
{/if}
