{assign var="anyOption" value=$anyOption|default:false}
{assign var="selectedStatus" value=$selectedStatus|default:false}

<select name="{$name}" class="form-control">
  {if $anyOption}
    <option value="">oricare</option>
  {/if}
  {foreach Definition::$STATUS_NAMES as $submitValue => $displayName}
    <option value="{$submitValue|escape}"
            {if $submitValue === $selectedStatus}selected{/if}>
      {$displayName|escape}
    </option>
  {/foreach}
</select>
