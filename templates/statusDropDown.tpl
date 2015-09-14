<select name="{$name}">
  {foreach from=$statuses key=submitValue item=displayName}
    <option value="{$submitValue|escape}"
            {if $submitValue == $selectedStatus}selected="selected"{/if}>
      {$displayName|escape}
    </option>
  {/foreach}
</select>
