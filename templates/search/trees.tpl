{foreach $trees as $t}
  <div class="tree">
    <div class="tree-heading">
      {$t->description}
      {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
        <a href="{$wwwRoot}editTree.php?id={$t->id}" class="pull-right">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      {/if}
    </div>
    <div class="tree-body">
      {include "bits/meaningTree.tpl" meanings=$t->getMeanings()}

      <h4 class="etymology">Etimologie:</h4>
      {include "bits/meaningTree.tpl" meanings=$t->getEtymologies() etymologies=true}
    </div>
  </div>
{/foreach}
