{assign var="id" value=$id|default:"structStatus"}
{assign var="name" value=$name|default:"structStatus"}
{assign var="selected" value=$selected|default:false}
{assign var="canEdit" value=$canEdit|default:false}

{foreach Entry::$STRUCT_STATUS_NAMES as $i => $s}
  <label class="radio-inline">
    <input type="radio"
           name="{$name}"
           value="{$i}"
           {if $i == $selected}checked{/if}
           {if !$canEdit}disabled{/if}>
    {$s}
  </label>
{/foreach}

{if !$canEdit}
  <input type="hidden" name="{$name}" value="{$selected}">
{/if}
