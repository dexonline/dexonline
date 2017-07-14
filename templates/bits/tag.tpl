{$link=$link|default:false}

{$style="color: {$t->getColor()}; background: {$t->getBackground()};"}

{strip}
<a href="{$wwwRoot}eticheta.php?id={$t->id}"
   class="label label-default {if !$link}disabled{/if}"
   {if !$link}disabled tabindex="-1"{/if}
   style="{$style}">
  {if $t->icon}
    <i class="tagIcon glyphicon glyphicon-{$t->icon}"></i>
  {/if}
  {$t->value}
</a>
{/strip}
