{* Recursively displays a meaning tree. *}
{$class=$class|default:'meaningTree'}
{$etymologies=$etymologies|default:false}
{* For CSS / JS purposes, there is no distinction from depth 2 onwards *}
{$depth=$depth|default:0|min:2}
{if $meanings}
  <ul class="{$class}">
    {foreach $meanings as $t}
      <li class="{$t.meaning->getCssClass()} depth-{$depth}">
        <div id="meaning{$t.meaning->id}" class="meaningContainer">

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

            <span class="tree-def html">
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
          {include "bits/meaningTree.tpl" class="" meanings=$t.examples depth=$depth+1}
        {/if}

        {include "bits/meaningTree.tpl" class="" meanings=$t.children depth=$depth+1}

        {if !empty($t.expressions)}
          {include "bits/meaningTree.tpl" class="" meanings=$t.expressions depth=$depth+1}
        {/if}

      </li>
    {/foreach}
  </ul>
{/if}
