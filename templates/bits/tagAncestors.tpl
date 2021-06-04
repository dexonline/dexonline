{foreach $tag->getAncestors() as $i => $t}
  {if $i}
    {include "bits/icon.tpl" i=chevron_right}
  {/if}
  {include "bits/tag.tpl" link=true}
{/foreach}
