{* Recursively displays a meaning tree. The id, if not empty, is only set for the root <ul>. *}
{if $meanings || $id}
  <ul {if $id}id="{$id}" class="meaningTree"{/if}>
    {foreach $meanings as $t}
      <li>
        <div class="meaningContainer"
             data-meaning-id="{$t.meaning->id}"
             {if !$t.canDelete}data-no-delete{/if}>
          <span class="bc"></span>
          <span class="type">{$t.meaning->type}</span>
          <span class="typeName">{$t.meaning->getDisplayTypeName()}</span>
          <span class="tags">
            {foreach $t.tags as $tag}
              <span class="meaningTag">{$tag->value}</span>
            {/foreach}
          </span>
          <span class="htmlRep">{$t.meaning->htmlRep}</span>
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
          
          <span class="id">{$t.meaning->id}</span>
          <span class="internalRep">{$t.meaning->internalRep}</span>
          <span class="tagIds">
            {foreach $t.tags as $tag}
              <span>{$tag->id}</span>
            {/foreach}
          </span>
          <span class="sourceIds">
            {foreach $t.sources as $s}
              <span>{$s->id}</span>
            {/foreach}
          </span>
          {foreach $t.relations as $type => $treeList}
            <span class="relationIds" data-type="{$type}">
              {foreach $treeList as $tree}
                <span>{$tree->id}</span>
              {/foreach}
            </span>
          {/foreach}
        </div>
        {include "bits/editableMeaningTree.tpl" meanings=$t.children id=""}
      {/strip}</li>
    {/foreach}
  </ul>
{/if}
