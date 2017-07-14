{* Recursively displays a tag tree (or forest). *}
{if $tags}
  <ul>
    {foreach $tags as $t}
      <li>
        {include "bits/tag.tpl" link=true}
        {if count($t->children)}
          <i class="expand glyphicon glyphicon-chevron-down"></i>
        {/if}
        {include "bits/tagTree.tpl" tags=$t->children}
      </li>
    {/foreach}
  </ul>
{/if}
