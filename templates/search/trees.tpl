{foreach $trees as $t}
  <div class="panel panel-default tree">
    <div class="panel-heading">
      {$t->description}
      {if $sUser && $sUser->moderator & ($smarty.const.PRIV_EDIT + $smarty.const.PRIV_STRUCT)}
        <a href="{$wwwRoot}editTree.php?id={$t->id}" class="pull-right">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      {/if}
    </div>
    <div class="panel-body">
      {include "bits/meaningTree.tpl" meanings=$t->getMeanings()}
    </div>
  </div>
{/foreach}
