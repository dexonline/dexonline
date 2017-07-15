{* Recursively displays a tag tree (or forest). *}
{if $tags}
  <ul>
    {foreach $tags as $t}
      <li>
        <i class="expand glyphicon {if count($t->children)}glyphicon-chevron-down{/if}"></i>
        {include "bits/tag.tpl" link=true}
        {include "bits/tagTree.tpl" tags=$t->children}
      </li>
    {/foreach}
  </ul>
{/if}
