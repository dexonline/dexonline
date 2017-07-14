{$link=$link|default:false}

{$style="color: {$t->getColor()}; background: {$t->getBackground()};"}

{strip}
{if $link}
  <a href="{$wwwRoot}eticheta.php?id={$t->id}"
     class="label label-default"
     style="{$style}">
    {$t->value}
  </a>
{else}
  <span class="label label-default"
        style="{$style}">
    {$t->value}
  </span>
{/if}
{/strip}
