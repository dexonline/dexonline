{$labels=$labels|default:true}
{foreach $relations as $type => $treeList}
  {if !empty($treeList)}
    <span class="tag-group">
      {if $labels}
        <span class="text-muted">{Relation::$TYPE_NAMES[$type]}:</span>
      {/if}
      {foreach $treeList as $tree}
        {$entries=$tree->getEntries()}
        <span class="label label-relation-{$type}">
          <a href="{$wwwRoot}intrare/{$tree->description}/{$entries[0]->id}">
            {$tree->description}
          </a>
        </span>
      {/foreach}
    </span>
  {/if}
{/foreach}
