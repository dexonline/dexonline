{assign var="id" value=$id|default:"structStatus"}
{assign var="name" value=$name|default:"structStatus"}
{assign var="structStatusNames" value=$structStatusNames}
{assign var="selected" value=$selected|default:false}
{assign var="canEdit" value=$canEdit|default:false}
{assign var="anyOption" value=$anyOption|default:false}

<select id="{$id}" name="{$name}" class="form-control" {if !$canEdit}disabled{/if}>
  {if $anyOption}
    <option value="0">oricare</option>
  {/if}
  {foreach $structStatusNames as $i => $s}
    <option value="{$i}" {if $i == $selected}selected{/if}>{$s}</option>
  {/foreach}
</select>

{if !$canEdit}
  <input type="hidden" name="{$name}" value="{$selected}">
{/if}
