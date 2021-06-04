{* Recursively displays a tag tree (or forest). *}
{if $tags}
  <ul>
    {foreach $tags as $t}
      <li>
        {if count($t->children)}
          {include "bits/icon.tpl" i=expand_more class="expand"}
        {/if}
        {include "bits/tag.tpl" link=$link}
        {include "bits/tagTree.tpl" tags=$t->children link=$link}
      </li>
    {/foreach}
  </ul>
{/if}
