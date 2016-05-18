{* A form-group field *}
{$type=$type|default:"text"}
{$field=$field|default:null}
{$value=$value|default:""}
{$step=$step|default:""}
{$label=$label|default:""}
{$placeholder=$placeholder|default:""}
{$autofocus=$autofocus|default:false}
<div class="form-group {if isset($errors.$field)}has-error{/if}">
  <label for="{$field}">{$label}</label>
  <input type="{$type}"
         {if $step}step="{$step}"{/if}
         class="form-control"
         id="{$field}"
         name="{$field}"
         value="{$value}"
         placeholder="{$placeholder}"
         {if $autofocus}autofocus{/if}>
  {include "bits/fieldErrors.tpl" errors=$errors.$field|default:null}
</div>
