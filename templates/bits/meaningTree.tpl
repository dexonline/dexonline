{* Recursively displays a meaning tree. *}
{$class=$class|default:'meaningTree'}
{$etymologies=$etymologies|default:false}
{$root=$root|default:true}
{if $meanings}
  <ul class="{$class}">
    {foreach $meanings as $t}
      <li>
        <div
          id="meaning{$t.meaning->id}"
          class="meaningContainer {if $root}primaryMeaning{else}secondaryMeaning{/if}">

          <div>
            {if $etymologies}
              {if $t.lastBreadcrumb}
                <span class="etymologyBc">({$t.lastBreadcrumb})</span>
              {/if}
            {else}
              <span class="bc">{$t.meaning->breadcrumb}</span>
              <span class="typeName">{$t.meaning->getDisplayTypeName()}</span>
            {/if}

            {include "bits/meaningTags.tpl" tags=$t.tags}

            <span class="def html {$t.meaning->getCssClass()}">
              {HtmlConverter::convert($t.meaning)}
              {$t.meaning->getDisplaySynonyms()}
            </span>

            {include "bits/meaningSources.tpl" sources=$t.sources}
          </div>

          <div class="defDetails">
            {include "bits/meaningRelations.tpl" relations=$t.relations defaultLabel=true}
          </div>

        </div>

        {if !empty($t.examples)}
          <div class="meaning-examples">
            {include "bits/meaningTree.tpl" class="" meanings=$t.examples root=false}
          </div>
        {/if}

        {include "bits/meaningTree.tpl" class="subtree" meanings=$t.children root=false}
      </li>
    {/foreach}
  </ul>
{/if}
