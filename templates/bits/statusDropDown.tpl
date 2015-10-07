<select name="{$name}">
  {foreach from=Definition::$STATUS_NAMES key=submitValue item=displayName}
    <option value="{$submitValue|escape}"
            {if $submitValue == $selectedStatus}selected="selected"{/if}>
      {$displayName|escape}
    </option>
  {/foreach}
</select>
