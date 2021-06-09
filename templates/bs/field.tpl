{* A form field *}
{* Mandatory arguments: $name *}
{$fieldErrors=$fieldErrors|default:null}
{$help=$help|default:false}
{$inputId=$inputId|default:null}
{$label=$label|default:''}
{$type=$type|default:'text'}
{$value=$value|default:''}

{if !$inputId}
  {$inputId='field-'|uniqid}
{/if}

<div class="mb-2">

  <label
    for="{$inputId}"
    class="form-label">
    {$label}
  </label>

  <input
    type="{$type}"
    class="form-control {if isset($fieldErrors)}is-invalid{/if}"
    id="{$inputId}"
    name="{$name}"
    value="{$value|escape}">

  {if $help}
    <div class="form-text">{$help}</div>
  {/if}

  {if $fieldErrors}
    {include "bits/fieldErrors.tpl" errors=$fieldErrors}
  {/if}

</div>
