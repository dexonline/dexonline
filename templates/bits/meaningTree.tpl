{* Recursively displays a meaning tree (or forest). The id, if not empty, is only set for the root <ul>. *}
{$editable=$editable|default:false}
{if $meanings || $id}
  <ul {if $id}id="{$id}" class="meaningTree"{/if}>
    {foreach $meanings as $t}
      <li>{strip}
        <div class="meaningContainer">
          <span class="bc"></span>
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
          
          {if $editable}
            <span class="id">{$t.meaning->id}</span>
            <span class="internalRep">{$t.meaning->internalRep}</span>
            <span class="internalEtymology">{$t.meaning->internalEtymology}</span>
            <span class="internalComment">{$t.meaning->internalComment}</span>
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
          {/if}
        </div>
        {include file="bits/meaningTree.tpl" meanings=$t.children id=""}
      {/strip}</li>
    {/foreach}
  </ul>
{/if}
