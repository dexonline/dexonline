{extends "layout.tpl"}

{block "title"}{'contact us'|_|cap}{/block}

{block "content"}

  <h3>{'contact us'|_|cap}</h3>

  <p class="text-medium">
    <a href="mailto:{$cfg.global.contact|escape}">
      <i class="glyphicon glyphicon-envelope"></i>
      {$cfg.global.contact|escape}
    </a>
  </p>

  <h3>{'Useful information'|_}</h3>

  <ul>
    <li>
      <a href="https://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Ce_nu_este_dexonline">
        {'What dexonline is <strong>not</strong>'|_}
      </a>
      {'(ways we cannot help you).'|_}
    </li>

    <li>
      {'You can install dexonline on <a href="%s">your computer or phone</a>.'|_|sprintf
      :"unelte"}
    </li>

    <li>
      {'You can download a <a href="%s">copy of the database</a>.'|_|sprintf
      :"https://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Desc.C4.83rcare"}
    </li>
  </ul>

{/block}
