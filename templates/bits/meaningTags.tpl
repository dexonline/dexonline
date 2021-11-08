{if count($tags)}
  <span class="tag-group meaning-tags">
    {foreach $tags as $tag}
      {include "bits/tag.tpl" t=$tag colors=false}
    {/foreach}
  </span>
{/if}
