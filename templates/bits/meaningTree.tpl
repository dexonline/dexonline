{* Recursively displays a meaning tree. *}
{$root=$root|default:true}
{if $meanings}
  <ul {if $root}class="meaningTree"{/if}>
    {foreach $meanings as $t}
      <li>
        <div class="meaningContainer">
          <div>
            <span class="bc">{$t.meaning->breadcrumb}</span>
            <span class="typeName">{$t.meaning->getDisplayTypeName()}</span>
            <span class="htmlRep">{$t.meaning->htmlRep}</span>
          </div>

          <div class="defDetails"">
            {if count($t.sources)}
              <span class="tag-group">
                <span class="text-muted">surse:</span>
                {foreach $t.sources as $s}
                  <span class="label label-source"
                        title="{$s->name}, {$s->year}">{$s->shortName}</span>
                {/foreach}
              </span>
            {/if}

            {if count($t.tags)}
              <span class="tag-group">
                <span class="text-muted">etichete:</span>
                {foreach $t.tags as $tag}
                  <span class="label label-tag">{$tag->value}</span>
                {/foreach}
              </span>
            {/if}

            {foreach $t.relations as $type => $treeList}
              {if !empty($treeList)}
                <span class="tag-group">
                  <span class="text-muted">{Relation::$TYPE_NAMES[$type]}:</span>
                  {foreach $treeList as $tree}
                    {$entries=$tree->getEntries()}
                    <span class="label label-relation-{$type}">
                      <a href="{$wwwRoot}/intrare/{$tree->description}/{$entries[0]->id}">
                        {$tree->description}
                      </a>
                    </span>
                  {/foreach}
                </span>
              {/if}
            {/foreach}
          </div>

        </div>
        {include "bits/meaningTree.tpl" meanings=$t.children root=false}
      </li>
    {/foreach}
  </ul>
{/if}
