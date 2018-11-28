{extends "layout.tpl"}

{block "title"}
  {cap}{t}contact us{/t}{/cap}
{/block}

{block "content"}

  <h3>{cap}{t}contact us{/t}{/cap}</h3>

  <p class="text-medium">
    <a href="mailto:{$cfg.mail.contact|escape}">
      <i class="glyphicon glyphicon-envelope"></i>
      {$cfg.mail.contact|escape}
    </a>
  </p>

  <h3>{t}Useful information{/t}</h3>

  <ul>
    <li>
      <a href="https://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Ce_nu_este_dexonline">
        {t}What dexonline is <strong>not</strong>{/t}
      </a>
      {t}(ways we cannot help you).{/t}
    </li>

    <li>
      {t 1="unelte"}
      You can install dexonline on <a href="%1">your computer or phone</a>.
      {/t}
    </li>

    <li>
      {t 1="https://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Desc.C4.83rcare"}
      You can download a <a href="%1">copy of the database</a>.
      {/t}
    </li>
  </ul>

{/block}
