{assign var="selected" value=$selected|default:null}
<select class="form-control" name="{$name}">
  {foreach $data as $key => $value}
    <option value="{$key}" {if $key == $selected}selected{/if}>
      {$value}
    </option>
  {/foreach}
</select>
