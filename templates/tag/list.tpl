{extends "layout-admin.tpl"}

{block "title"}Etichete{/block}

{block "content"}
  <h3>Etichete</h3>

  {if User::can(User::PRIV_EDIT)}
    <a class="btn btn-primary" href="{Router::link('tag/edit')}">
      {include "bits/icon.tpl" i=add}
      adaugă o etichetă
    </a>
  {/if}

  <div id="tagTree" class="voffset3">
    {include "bits/tagTree.tpl" link=User::can(User::PRIV_EDIT)}
  </div>

{/block}
