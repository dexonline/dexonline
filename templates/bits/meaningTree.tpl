{* Recursively displays a meaning tree (or forest). The id, if not empty, is only set for the root <ul>. *}
{if $meanings || $id}
  <ul {if $id}id="{$id}"{/if}>
    {foreach from=$meanings item=t}
      <li>{strip}
        <div class="meaningContainer">
          <span class="id">{$t.meaning->id}</span>
          <span class="breadcrumb"></span>
          <span class="meaningTags">
            {foreach from=$t.tags item=tag}
              <span class="tag">{$tag->value}</span>
            {/foreach}
          </span>
          <span class="meaningTagIds">
            {foreach from=$t.tags item=tag name=tagLoop}
              {$tag->id}{if !$smarty.foreach.tagLoop.last},{/if}
            {/foreach}
          </span>
          <span class="internalRep">{$t.meaning->internalRep}</span>
          <span class="htmlRep">{$t.meaning->htmlRep}</span>
          <span class="internalEtymology">{$t.meaning->internalEtymology}</span>
          <span class="htmlEtymology">{$t.meaning->htmlEtymology}</span>
          <span class="internalComment">{$t.meaning->internalComment}</span>
          <span class="htmlComment">{$t.meaning->htmlComment}</span>
          <span class="sources">
            {foreach from=$t.sources item=s}
              <span class="tag">{$s->shortName}</span>
            {/foreach}
          </span>
          <span class="sourceIds">
            {foreach from=$t.sources item=s name=sourceLoop}
              {$s->id}{if !$smarty.foreach.sourceLoop.last},{/if}
            {/foreach}
          </span>
          {foreach from=$t.relations key=type item=lexemList}
            <span class="relation" data-type="{$type}">
              {foreach from=$lexemList item=l}
                <span class="tag">{include file="bits/lexemName.tpl" lexem=$l}</span>
              {/foreach}
            </span>
            <span class="relationIds" data-type="{$type}">
              {foreach from=$lexemList item=l name=lexemLoop}
                {$l->id}{if !$smarty.foreach.lexemLoop.last},{/if}
              {/foreach}
            </span>
          {/foreach}
        </div>
        {include file="bits/meaningTree.tpl" meanings=$t.children id=""}
      {/strip}</li>
    {/foreach}
  </ul>
{/if}
