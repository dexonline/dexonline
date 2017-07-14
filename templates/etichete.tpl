{extends "layout-admin.tpl"}

{block "title"}Etichete pentru sensuri{/block}

{block "content"}
  <h3>Etichete pentru sensuri</h3>

  <div class="row">
    <div class="col-md-7 col-md-offset-3">
      <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        Clic pe orice linie pentru a obține un meniu. Nu uitați să salvați la sfârșit.
      </div>
    </div>
  </div>
  <li id="stem">
    <div class="expand"></div>
    <div class="value" data-id="" data-can-delete="1"></div>
  </li>
  {include "bits/tagTree.tpl" tags=$tags id="tagTree"}

  <div id="menuBar">
    <input type="text" name="value" value="" id="valueBox" size="20">
    <div id="menuActions">
      <button class="btn btn-xs btn-default" id="butUp"
              title="Eticheta schimbă locurile cu fratele său anterior."
              >⇧</button>
      <button class="btn btn-xs btn-default" id="butDown"
              title="Eticheta schimbă locurile cu fratele său următor."
              >⇩</button>
      <button class="btn btn-xs btn-default" id="butLeft"
              title="Eticheta devine fratele următor al tatălui său."
              >⇦</button>
      <button class="btn btn-xs btn-default" id="butRight"
              title="Eticheta devine fiu al fratelui său anterior."
              >⇨</button>
      <button class="btn btn-xs btn-default" id="butAddSibling"
              title="Adaugă o etichetă ca frate al etichetei selectate."
              >adaugă frate</button>
      <button class="btn btn-xs btn-default" id="butAddChild"
              title="Adaugă o etichetă ca ultim fiu al etichetei selectate."
              >adaugă fiu</button>
      <button class="btn btn-xs btn-danger" id="butDelete"
              title="Șterge eticheta."
              >șterge</button>
      <button class="btn btn-xs btn-info" id="butDetails" data-href="eticheta.php?id="
              title="Detalii despre folosirea etichetei."
        >
        <i class="glyphicon glyphicon-eye-open"></i>
        detalii
      </button>
    </div>
  </div>

  <form method="post" action="etichete">
    <input type="hidden" name="jsonTags" value="">

    <button class="btn btn-success" type="submit" id="butSave" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>

    <a class="btn btn-default" href="eticheta">
      <i class="glyphicon glyphicon-plus"></i>
      adaugă o etichetă
    </a>

  </form>
{/block}
