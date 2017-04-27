<select name="{$name}" class="form-control">
  {foreach Definition::$STATUS_NAMES as $submitValue => $displayName}
    <option value="{$submitValue|escape}"
            {if $submitValue == $selectedStatus}selected{/if}>
      {$displayName|escape}
    </option>
  {/foreach}
</select>
