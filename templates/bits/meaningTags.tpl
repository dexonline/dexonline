{if count($tags)}
  <span class="tag-group">
    {foreach $tags as $tag}
      {include "bits/tag.tpl" t=$tag colors=false}
    {/foreach}
  </span>
{/if}
