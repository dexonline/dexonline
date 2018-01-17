{* Makes a select field group with granularity engine names *}

{assign var="id" value=$id|default:"engine"}
{assign var="name" value=$name|default:"engine"}
{assign var="selected" value=$selected|default:false}
{assign var="canEdit" value=$canEdit|default:false}
{assign var="anyOption" value=$anyOption|default:false}

<select id="{$id}"
        name="{$name}"
        class="form-control"
        tabindex="-1"
        {if !$canEdit}disabled{/if}>
  {if $anyOption}
    <option value="0">oricare</option>
  {/if}
  {foreach DiffUtil::$DIFF_ENGINE_NAMES as $i => $s}
    <option value="{$i}" {if $i == $selected}selected{/if}>{$s}</option>
  {/foreach}
</select>

{if !$canEdit}
  <input type="hidden" name="{$name}" value="{$selected}">
{/if}
