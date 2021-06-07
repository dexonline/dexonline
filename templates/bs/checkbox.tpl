{* mandatory args: $name, $label *}
{$checked=$checked|default:false}
{$divClass=$divClass|default:''}
{$errors=$errors|default:null}
{$help=$help|default:false}
{$inputId=$inputId|default:null}
{$value=$value|default:null}

{if !$inputId}
  {$inputId='cb-'|uniqid}
{/if}

<div class="form-check {$divClass}">
  <input
    id="{$inputId}"
    type="checkbox"
    class="form-check-input {if $errors}is-invalid{/if}"
    {if $name}name="{$name}"{/if}
    {if $value}value="{$value}"{/if}
    {if $checked}checked{/if}>
  <label for="{$inputId}" class="form-check-label">
    {$label}
  </label>
  {if $help}
    <div class="form-text">{$help}</div>
  {/if}
  {if $errors}
    {include "bits/fieldErrors.tpl"}
  {/if}
</div>
