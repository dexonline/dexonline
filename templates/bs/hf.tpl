{* A horizontal form field *}
{* Mandatory arguments: $name *}
{$autofocus=$autofocus|default:false}
{$breakpoint=$breakpoint|default:'xl'}
{$col=$col|default:2}
{$hfErrors=$hfErrors|default:null}
{$inputId=$inputId|default:null}
{$label=$label|default:''}
{$placeholder=$placeholder|default:''}
{$readonly=$readonly|default:false}
{$type=$type|default:'text'}
{$value=$value|default:''}

{if !$inputId}
  {$inputId='hf-'|uniqid}
{/if}

<div class="row mb-2">

  <label
    for="{$inputId}"
    class="col-{$breakpoint}-{$col} col-form-label">
    {$label}
  </label>

  <div class="col-{$breakpoint}-{12-$col}">
    <input
      type="{$type}"
      class="form-control {if isset($hfErrors)}is-invalid{/if}"
      id="{$inputId}"
      name="{$name}"
      value="{$value|escape}"
      placeholder="{$placeholder}"
      {if $autofocus}autofocus{/if}
      {if $readonly}readonly{/if}>
    {if $hfErrors}
      {include "bits/fieldErrors.tpl" errors=$hfErrors}
    {/if}
  </div>

</div>
