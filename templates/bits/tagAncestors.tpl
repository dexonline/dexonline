{foreach $tag->getAncestors() as $i => $t}
  {if $i}
    <i class="glyphicon glyphicon-chevron-right"></i>
  {/if}
  {include "bits/tag.tpl" link=true}
{/foreach}
