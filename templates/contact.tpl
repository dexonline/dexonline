{extends "layout.tpl"}

{block "title"}Contact{/block}

{block "content"}

  <h3>Contact</h3>

  <p class="text-medium">
    <a href="mailto:{$cfg.global.contact|escape}">
      <i class="glyphicon glyphicon-envelope"></i>
      {$cfg.global.contact|escape}
    </a>
  </p>

  <h3>Informații utile</h3>

  <ul>
    <li>
      <a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Ce_nu_este_dexonline">
        Ce <b>nu</b> este dexonline
      </a>
      (când nu vă putem ajuta).
    </li>

    <li>
      Puteți instala dexonline pe <a href="unelte">calculatorul sau telefonul dumneavoastră</a>.
    </li>

    <li>
      Puteți descărca o <a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Desc.C4.83rcare">copie a bazei de date</a>.
    </li>
  </ul>

{/block}
