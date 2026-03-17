{extends "layout.tpl"}

{block "title"}
  {cap}{t}contact us{/t}{/cap}
{/block}

{block "content"}
  <p><span class="just">
  <b>Notă</b>: Prin trimiterea unui e-mail către dexonline, sunteți de acord ca mesajul dumneavoastră și datele de
  identificare (numele, adresa de e-mail) să fie eventual publicate (integral sau parțial) pe site-ul nostru și/sau
  pe rețelele de socializare.
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

{/block}
