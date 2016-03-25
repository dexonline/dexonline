{extends file="layout.tpl"}

{block name=title}Etichete pentru sensuri{/block}

{block name=content}
  <h3>Etichete pentru sensuri</h3>

  <div class="flashMessage flashMessage-info">
    Clic pe orice linie pentru a obține un meniu. Nu uitați să salvați la sfârșit.
  </div>

  <li id="stem">
    <div class="expand"></div>
    <div class="value" data-id="" data-can-delete="1"></div>
  </li>
  {include file="bits/meaningTagTree.tpl" tags=$meaningTags id="mtTree"}

  <div id="menuBar">
    <input type="text" name="value" value="" id="valueBox" size="20">
    <div id="menuActions">
      <button id="butUp"
              title="Eticheta schimbă locurile cu fratele său anterior."
              >⇧</button>
      <button id="butDown"
              title="Eticheta schimbă locurile cu fratele său următor."
              >⇩</button>
      <button id="butLeft"
              title="Eticheta devine fratele următor al tatălui său."
              >⇦</button>
      <button id="butRight"
              title="Eticheta devine fiu al fratelui său anterior."
              >⇨</button>
      <button id="butAddSibling"
              title="Adaugă o etichetă ca frate al etichetei selectate."
              >adaugă frate</button>
      <button id="butAddChild"
              title="Adaugă o etichetă ca ultim fiu al etichetei selectate."
              >adaugă fiu</button>
      <button id="butDelete"
              title="Șterge eticheta."
              >șterge</button>
    </div>
  </div>

  <form method="post" action="etichete-sensuri">
    <input type="hidden" name="jsonTags" value="">
    <input type="submit" id="butSave" name="saveButton" value="salvează">
  </form>
{/block}
