{foreach $trees as $t}
  <h4 class="tree-heading">
    {$t->description}
    {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
      <small>
        <a href="{$wwwRoot}editTree.php?id={$t->id}" class="pull-right">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      </small>
    {/if}
  </h4>

  <div class="tree-body">
    {include "bits/meaningTree.tpl" meanings=$t->getMeanings()}

    <h4 class="etymology">etimologie:</h4>
    {include "bits/meaningTree.tpl" meanings=$t->getEtymologies() etymologies=true}
  </div>
{/foreach}
