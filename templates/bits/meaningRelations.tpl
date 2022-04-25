{$defaultLabel=$defaultLabel|default:false}
{foreach $relations as $type => $treeList}
  {if !empty($treeList)}
    <span class="tag-group me-3">
      {if $defaultLabel || ($type != Relation::DEFAULT_TYPE)}
        <span class="text-muted">{Relation::getTypeName($type)}:</span>
      {/if}
      {foreach $treeList as $tree}
        {$entries=$tree->getEntries()}
        <span class="badge-relation badge-relation-{$type}">
          <a href="{Config::URL_PREFIX}intrare/{$tree->getShortDescription()}/{$entries[0]->id}">
            {$tree->getShortDescription()}
          </a>
        </span>
      {/foreach}
    </span>
  {/if}
{/foreach}
