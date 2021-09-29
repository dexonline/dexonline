{extends "layout-admin.tpl"}

{block "title"}Arbori neasociați{/block}

{block "content"}

  <h3>{$trees|count} arbori neasociați</h3>

  {foreach $trees as $t}
    <div class="card mb-3 tree">
      <div class="card-header d-flex align-items-center">
        <span class="flex-grow-1">
          {$t->description}
        </span>

        <a href="{Router::link('tree/edit')}?id={$t->id}" class="btn btn-outline-secondary btn-sm">
          {include "bits/icon.tpl" i=edit}
          editează
        </a>
        <a href="#" class="deleteLink btn btn-danger btn-sm ms-1" data-id="{$t->id}">
          {include "bits/icon.tpl" i=delete}
          șterge
        </a>
      </div>

      <div class="card-body">
        {include "bits/meaningTree.tpl" meanings=$t->getMeanings() id="meaningTree-{$t->id}"}
      </div>
    </div>
  {/foreach}

{/block}
