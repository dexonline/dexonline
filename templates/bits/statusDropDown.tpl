{assign var="anyOption" value=$anyOption|default:false}
{assign var="selectedStatus" value=$selectedStatus|default:false}
{assign var="disabled" value=$disabled|default:false}

<select name="{$name}" class="form-control" {if $disabled}disabled{/if}>
  {if $anyOption}
    <option value="">oricare</option>
  {/if}
  {foreach Definition::STATUS_NAMES as $submitValue => $displayName}
    <option value="{$submitValue|escape}"
            {if (string)$submitValue == (string)$selectedStatus}selected{/if}>
      {$displayName|escape}
    </option>
  {/foreach}
</select>

{if $disabled}
  <input type="hidden" name="{$name}" value="{$selectedStatus}">
{/if}
