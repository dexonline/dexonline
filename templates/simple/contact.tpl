{extends "layout.tpl"}

{block "title"}
  {cap}{t}contact us{/t}{/cap}
{/block}

{block "content"}
  <p><span class="just">
  <b>Notă</b>: Prin trimiterea unui email către dexonline, sunteți de acord ca mesajul dumneavoastră să fie (eventual)
  publicat (integral sau parțial) pe site-ul nostru și/sau pe rețelele de socializare.
  </span>
  </p>

  <h3>{cap}{t}contact us{/t}{/cap}</h3>

  <p class="fs-4 ps-3">
    <a href="mailto:{Config::CONTACT_EMAIL|escape}">
      {include "bits/icon.tpl" i=email}
      {Config::CONTACT_EMAIL|escape}
    </a>
  <p>

  <h3>{cap}newsletter{/cap}</h3>

  <p class="fs-4 ps-3">
    <a href="mailto:newsletter@dexonline.ro">
        {include "bits/icon.tpl" i=email}
        newsletter@dexonline.ro
    </a>
  <p>

  <h3>{t}Useful information{/t}</h3>

  <ul>
    <li>
      {t 1="https://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Ce_nu_este_dexonline"}
      <a href="%1">What dexonline is <strong>not</strong></a>
      (ways we cannot help you).
      {/t}
    </li>

    <li>
      {t 1=Router::link('simple/tools')}
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
