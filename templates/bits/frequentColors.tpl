{* used on the tag edit page *}
<small class="form-text">
  {t}culori frecvente{/t}:
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
