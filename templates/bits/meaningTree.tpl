{* Recursively displays a meaning tree. *}
{$root=$root|default:true}
{$etymologies=$etymologies|default:false}
{if $meanings}
  <ul {if $root}class="meaningTree"{/if}>
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
              {if $root && ($totalTreeSize > Config::SEARCH_COLLAPSE_ROOTS)}
                <a class="collapse-root muted-link text-decoration-none" href="#">
                  {include "bits/icon.tpl" i=expand_less}
                </a>
              {/if}
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

            {if !empty($t.examples)}
              {$collapseId="exampleCollapse_{$t.meaning->id}"}
              <a
                data-bs-toggle="collapse"
                class="muted-link me-3"
                href="#{$collapseId}"
                aria-controls="{$collapseId}">
                {include "bits/icon.tpl" i=attach_file}
                {t count=$t.examples|@count 1=$t.examples|@count plural="%1 examples"}
                one example
                {/t}</a>
            {/if}

          </div>

        </div>

        {if !empty($t.examples)}
          <div class="examples collapse" id="{$collapseId}">
            <div class="card">
              <div class="card-header">exemple</div>
              <div class="card-body">
                {include "bits/meaningTree.tpl" meanings=$t.examples root=false}
              </div>
            </div>
          </div>
        {/if}

        {include "bits/meaningTree.tpl" meanings=$t.children root=false}
      </li>
    {/foreach}
  </ul>
{/if}
