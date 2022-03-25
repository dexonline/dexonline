{extends "layout-admin.tpl"}

{block "title"}Etichete{/block}

{block "content"}
  <h3 class="mb-3">Etichete</h3>

  <div class="mb-3">
    {if User::can(User::PRIV_EDIT)}
      <a class="btn btn-sm btn-primary" href="{Router::link('tag/edit')}">
        {include "bits/icon.tpl" i=add}
        adaugă o etichetă
      </a>
    {/if}

    <a
      class="btn btn-sm btn-outline-secondary doubleText"
      data-other-text="{t}link-collapse-all{/t}"
      href="#"
      id="link-expand-all">
      {t}link-expand-all{/t}
    </a>
  </div>

  <div id="tag-tree">
    {include "bits/tagTree.tpl" link=User::can(User::PRIV_EDIT)}
  </div>

{/block}
