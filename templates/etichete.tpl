{extends "layout-admin.tpl"}

{block "title"}Etichete pentru sensuri{/block}

{block "content"}
  <h3>Etichete</h3>

  <a class="btn btn-default" href="eticheta">
    <i class="glyphicon glyphicon-plus"></i>
    adaugă o etichetă
  </a>

  <div id="tagTree" class="voffset3">
    {include "bits/tagTree.tpl"}
  </div>

{/block}
