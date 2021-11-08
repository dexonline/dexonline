{* used on the tag edit page *}
{$class=$class|default:''}
<small class="form-text">
  culori frecvente:
  {foreach $colors as $color}
    <span
      data-value="{$color}"
      data-target="{$target}"
      class="frequent-color {$class}"
      {if $color}style="background: {$color}"{/if}>
      &nbsp;
    </span>
  {/foreach}
</small>
