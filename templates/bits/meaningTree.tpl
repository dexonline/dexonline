{* Recursively displays a meaning tree. *}
{$class=$class|default:'meaningTree'}
{$etymologies=$etymologies|default:false}
{$primary=$primary|default:true}
{if $meanings}
  <ul class="{$class}">
    {foreach $meanings as $t}
      <li class="{$t.meaning->getCssClass()}">
        <div
          id="meaning{$t.meaning->id}"
          class="meaningContainer {if $primary}primaryMeaning{/if}">

          <div class="meaning-row">
            {$icon=$t.meaning->getIcon()}
            {if $icon}
              {include "bits/icon.tpl" i=$icon class="meaning-icon"}
            {/if}

            {if $etymologies}
              {if $t.lastBreadcrumb}
                <span class="etymologyBc">({$t.lastBreadcrumb})</span>
              {/if}
            {else}
              <span class="bc">{$t.meaning->breadcrumb}</span>
              <span class="typeName">{$t.meaning->getDisplayTypeName()}</span>
            {/if}

            {include "bits/meaningTags.tpl" tags=$t.tags}

            <span class="def html">
              {HtmlConverter::convert($t.meaning)}
              {$t.meaning->getDisplaySynonyms()}
            </span>

            {include "bits/meaningSources.tpl" sources=$t.sources}
          </div>

          <div class="meaning-relations">
            {include "bits/meaningRelations.tpl" relations=$t.relations defaultLabel=true}
          </div>

        </div>

        {if !empty($t.examples)}
          {include "bits/meaningTree.tpl" class="subtree" meanings=$t.examples primary=false}
        {/if}

        {include "bits/meaningTree.tpl" class="subtree" meanings=$t.children primary=false}

        {if !empty($t.expressions)}
          {include "bits/meaningTree.tpl" class="subtree" meanings=$t.expressions primary=false}
        {/if}

      </li>
    {/foreach}
  </ul>
{/if}
