{extends "layout-admin.tpl"}

{block "title"}Etichete pentru sensuri{/block}

{block "content"}
  <h3>Etichete</h3>

  {if User::can(User::PRIV_EDIT)}
    <a class="btn btn-default" href="eticheta">
      <i class="glyphicon glyphicon-plus"></i>
      adaugă o etichetă
    </a>
  {/if}

  <div id="tagTree" class="voffset3">
    {include "bits/tagTree.tpl" link=User::can(User::PRIV_EDIT)}
  </div>

{/block}
