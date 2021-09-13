{* used on the tag edit page *}
<small class="form-text">
  culori frecvente:
  {foreach $colors as $color}
    <span
      data-value="{$color}"
      data-target="{$target}"
      class="frequent-color"
      style="background: {$color}">
      &nbsp;
    </span>
  {/foreach}
</small>
