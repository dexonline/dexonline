{assign var="name" value=$name|default:"structStatus"}
{assign var="selected" value=$selected|default:false}
{assign var="anyOption" value=$anyOption|default:false}

<select name="{$name}" class="form-control">
  {if $anyOption}
    <option value="0">oricare</option>
  {/if}
  {foreach Entry::$STRUCT_STATUS_NAMES as $i => $s}
    <option value="{$i}" {if $i == $selected}selected{/if}>{$s}</option>
  {/foreach}
</select>
