{$selected=$selected|default:null}
{$disabled=$disabled|default:false}
<select class="form-control" name="{$name}" {if $disabled}disabled{/if}>
  {foreach $data as $key => $value}
    <option value="{$key}" {if $key == $selected}selected{/if}>
      {$value}
    </option>
  {/foreach}
</select>
