{* A form-horizontal field *}
{$type=$type|default:"text"}
{$field=$field|default:null}
{$value=$value|default:""}
{$step=$step|default:""}
{$label=$label|default:""}
{$placeholder=$placeholder|default:""}
{$autofocus=$autofocus|default:false}
{$readonly=$readonly|default:false}
{$col=$col|default:2}
<div class="form-group {if isset($errors.$field)}has-error{/if}">
  <label for="{$field}" class="col-md-{$col} control-label">{$label}</label>
  <div class="col-md-{12-$col}">
    <input type="{$type}"
           {if $step}step="{$step}"{/if}
           class="form-control"
           id="{$field}"
           name="{$field}"
           value="{$value|escape}"
           placeholder="{$placeholder}"
           {if $autofocus}autofocus{/if}
           {if $readonly}readonly{/if}>
    {include "bits/fieldErrors.tpl" errors=$errors.$field|default:null}
  </div>
</div>
