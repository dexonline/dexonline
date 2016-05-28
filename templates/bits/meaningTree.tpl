{* Recursively displays a meaning tree (or forest). The id, if not empty, is only set for the root <ul>. *}
{if $meanings || $id}
  <ul {if $id}id="{$id}"{/if}>
    {foreach $meanings as $t}
      <li>{strip}
        <div class="meaningContainer">
          <span class="id">{$t.meaning->id}</span>
          <span class="breadcrumb"></span>
          <span class="tags">
            {foreach $t.tags as $tag}
              <span class="tag">{$tag->value}</span>
            {/foreach}
          </span>
          <span class="tagIds">
            {foreach $t.tags as $tag}
              <span>{$tag->id}</span>
            {/foreach}
          </span>
          <span class="internalRep">{$t.meaning->internalRep}</span>
          <span class="htmlRep">{$t.meaning->htmlRep}</span>
          <span class="internalEtymology">{$t.meaning->internalEtymology}</span>
          <span class="htmlEtymology">{$t.meaning->htmlEtymology}</span>
          <span class="internalComment">{$t.meaning->internalComment}</span>
          <span class="htmlComment">{$t.meaning->htmlComment}</span>
          <span class="sources">
            {foreach $t.sources as $s}
              <span class="tag">{$s->shortName}</span>
            {/foreach}
          </span>
          <span class="sourceIds">
            {foreach $t.sources as $s}
              <span>{$s->id}</span>
            {/foreach}
          </span>
          {foreach $t.relations as $type => $lexemList}
            <span class="relation" data-type="{$type}">
              {foreach $lexemList as $l}
                <span class="tag">{include file="bits/lexemName.tpl" lexem=$l}</span>
              {/foreach}
            </span>
            <span class="relationIds" data-type="{$type}">
              {foreach $lexemList as $l}
                <span>{$l->id}</span>
              {/foreach}
            </span>
          {/foreach}
        </div>
        {include file="bits/meaningTree.tpl" meanings=$t.children id=""}
      {/strip}</li>
    {/foreach}
  </ul>
{/if}
