{* mandatory args: $name, $label *}
{$cbErrors=$cbErrors|default:null}
{$checked=$checked|default:false}
{$disabled=$disabled|default:false}
{$divClass=$divClass|default:''}
{$divId=$divId|default:''}
{$help=$help|default:false}
{$hidden=$hidden|default:false}
{$inputClass=$inputClass|default:''}
{$inputId=$inputId|default:null}
{$title=$title|default:null}
{$value=$value|default:null}

{if !$inputId}
  {$inputId='cb-'|uniqid}
{/if}

<div
  {if $divId}id="{$divId}"{/if}
  class="form-check {$divClass}"
  {if $hidden}hidden{/if}>

  <input
    id="{$inputId}"
    type="checkbox"
    class="form-check-input {$inputClass} {if $cbErrors}is-invalid{/if}"
    {if $name}name="{$name}"{/if}
    {if $value}value="{$value}"{/if}
    {if $checked}checked{/if}
    {if $disabled}disabled{/if}>

  <label
    for="{$inputId}"
    class="form-check-label"
    {if $title}title="{$title|escape}"{/if}>
    {$label}
  </label>

  {if $help}
    <div class="form-text">{$help}</div>
  {/if}

  {if $cbErrors}
    {include "bits/fieldErrors.tpl" errors=$cbErrors}
  {/if}

</div>
