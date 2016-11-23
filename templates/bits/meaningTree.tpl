{* Recursively displays a meaning tree. *}
{$root=$root|default:true}
{if $meanings}
  <ul {if $root}class="meaningTree"{/if}>
    {foreach $meanings as $t}
      <li>{strip}
        <div class="meaningContainer">
          <span class="bc">{$t.meaning->breadcrumb}</span>
          <span class="tags">
            {foreach $t.tags as $tag}
              <span class="meaningTag">{$tag->value}</span>
            {/foreach}
          </span>
          <span class="htmlRep">{$t.meaning->htmlRep}</span>
          <span class="htmlEtymology">{$t.meaning->htmlEtymology}</span>
          <span class="htmlComment">{$t.meaning->htmlComment}</span>
          <span class="sources">
            {foreach $t.sources as $s}
              <span class="meaningTag">{$s->shortName}</span>
            {/foreach}
          </span>
          {foreach $t.relations as $type => $treeList}
            <span class="relation" data-type="{$type}">
              {foreach $treeList as $tree}
                <span class="meaningTag">
                  {$tree->description}
                </span>
              {/foreach}
            </span>
          {/foreach}
        </div>
        {include "bits/meaningTree.tpl" meanings=$t.children root=false}
      {/strip}</li>
    {/foreach}
  </ul>
{/if}
