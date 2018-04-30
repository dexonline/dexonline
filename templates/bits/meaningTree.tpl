{* Recursively displays a meaning tree. *}
{$root=$root|default:true}
{$etymologies=$etymologies|default:false}
{if $meanings}
  <ul {if $root}class="meaningTree"{/if}>
    {foreach $meanings as $t}
      {$relationsShown=false}
      <li>
        <div class="meaningContainer {if $root}primaryMeaning{else}secondaryMeaning{/if}">
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

            {* When the meaning itself is empty, show something else *}
            <span class="def html {$t.meaning->getCssClass()}">
              {$t.meaning->getHtml()}
              {if $t.meaning->includeRelations()}
                {include "bits/meaningRelations.tpl" relations=$t.relations}
                {$relationsShown=true}
              {/if}
            </span>

          </div>

          <div class="defDetails">
            {if count($t.sources)}
              <span class="tag-group">
                <span class="text-muted">surse:</span>
                {foreach $t.sources as $s}
                  <span class="label label-source"
                        title="{$s->name}, {$s->year}">{$s->shortName}</span>
                {/foreach}
              </span>
            {/if}

            {if !$relationsShown}
              {include "bits/meaningRelations.tpl" relations=$t.relations defaultLabel=true}
            {/if}

            {if !empty($t.examples)}
              {$collapseId="exampleCollapse_{$t.meaning->id}"}
              <a data-toggle="collapse"
                 class="exampleLink"
                 href="#{$collapseId}"
                 aria-controls="{$collapseId}">
                <i class="glyphicon glyphicon-paperclip"></i>

                {include "bits/count.tpl"
                displayed=count($t.examples)
                one="un exemplu"
                many="exemple"}

                <span class="caret"></span>
              </a>
            {/if}

          </div>

        </div>

        {if !empty($t.examples)}
          <div class="examples collapse" id="{$collapseId}">
            <div class="panel panel-default">
              <div class="panel-heading">exemple</div>
              <div class="panel-body">
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
