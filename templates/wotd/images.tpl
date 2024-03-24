{extends "layout-admin.tpl"}

{block "title"}Catalog de imagini{/block}

{block "content"}

  <h3>Catalog de imagini</h3>

  <div id="fileManager"></div>

  <h3>Pagini asociate</h3>

  <ul>

    <li>
      <a href="{Router::link('wotd/table')}">lista cuvintelor zilei</a>
    </li>

    <li>
      <a href="https://wiki.dexonline.ro/wiki/Imagini_pentru_cuv%C3%A2ntul_zilei"
      >instrucțiuni</a>
    </li>

  </ul>

{/block}
