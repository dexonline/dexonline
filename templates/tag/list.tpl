{extends "layout-admin.tpl"}

{block "title"}Etichete{/block}

{block "content"}
  <h3 class="mb-3">Etichete</h3>

  {if User::can(User::PRIV_EDIT)}
    <a class="btn btn-primary mb-3" href="{Router::link('tag/edit')}">
      {include "bits/icon.tpl" i=add}
      adaugă o etichetă
    </a>
  {/if}

  <div id="tagTree">
    {include "bits/tagTree.tpl" link=User::can(User::PRIV_EDIT)}
  </div>

{/block}
