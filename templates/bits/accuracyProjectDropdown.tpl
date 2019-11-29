<select class="form-control" name="{$visibility.vars.name}" {if $visibility.vars.disabled}disabled{/if}>
  {foreach $visibility.resultSet as $key => $value}
    <option value="{$key}" {if $key == $visibility.vars.selectedValue}selected{/if}>
      {$value}
    </option>
  {/foreach}
</select>
