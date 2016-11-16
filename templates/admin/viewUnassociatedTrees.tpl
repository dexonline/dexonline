{extends "layout-admin.tpl"}

{block "title"}Arbori neasociați{/block}

{block "content"}

  <h3>{$trees|count} arbori neasociați</h3>

  {foreach $trees as $t}
    <div class="panel panel-default tree">
      <div class="panel-heading">
        {$t->description}

        <div class="pull-right">
          <a href="{$wwwRoot}editTree.php?id={$t->id}" class="btn btn-default btn-xs">
            <i class="glyphicon glyphicon-pencil"></i>
            editează
          </a>
          <a href="#" class="deleteLink btn btn-danger btn-xs" data-id="{$t->id}">
            <i class="glyphicon glyphicon-trash"></i>
            șterge
          </a>
        </div>
      </div>

      <div class="panel-body">
        {include "bits/meaningTree.tpl" meanings=$t->getMeanings() id="meaningTree-{$t->id}"}
      </div>
    </div>
  {/foreach}

{/block}
