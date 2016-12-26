{foreach $tag->getAncestors() as $i => $t}
  {if $i}
    <i class="glyphicon glyphicon-chevron-right"></i>
  {/if}
  <a href="{$wwwRoot}eticheta.php?id={$t->id}" class="label label-info">{$t->value}</a>
{/foreach}
