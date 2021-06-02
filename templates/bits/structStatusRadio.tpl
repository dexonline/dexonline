{assign var="id" value=$id|default:"structStatus"}
{assign var="name" value=$name|default:"structStatus"}
{assign var="selected" value=$selected|default:false}
{assign var="canEdit" value=$canEdit|default:false}

{foreach Entry::STRUCT_STATUS_NAMES as $i => $s}
  <div class="form-check form-check-inline">
    <input
      id="status-radio-{$i}"
      type="radio"
      class="form-check-input {if isset($errors.structStatus)}is-invalid{/if}"
      name="{$name}"
      value="{$i}"
      {if $i == $selected}checked{/if}
      {if !$canEdit}disabled{/if}>
    <label class="form-check-label" for="status-radio-{$i}">
      {$s}
    </label>
    {if $i == 4}
      {include "bits/fieldErrors.tpl" errors=$errors.structStatus|default:null}
    {/if}
  </div>
{/foreach}

{if !$canEdit}
  <input type="hidden" name="{$name}" value="{$selected}">
{/if}
