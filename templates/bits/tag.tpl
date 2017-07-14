{$link=$link|default:false}
{if $link}
  <a href="{$wwwRoot}eticheta.php?id={$t->id}"
     class="label label-default"
     style="color: {$t->getColor()}; background: {$t->getBackground()};">
    {$t->value}
  </a>
{else}
{/if}
