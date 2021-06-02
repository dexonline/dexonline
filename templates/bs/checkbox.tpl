{* mandatory args: $name, $label *}
{$checked=$checked|default:false}
<div class="form-check">
  <label class="form-check-label">
    <input
      type="checkbox"
      class="form-check-input"
      name="{$name}"
      {if $checked}checked{/if}>
    {$label}
  </label>
</div>
