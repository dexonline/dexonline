{extends file="layout.tpl"}

{block name=title}Autentificare cu OpenID{/block}

{block name=search}{/block}

{block name=content}
  {assign var="allowFakeUsers" value=$allowFakeUsers|default:false}
  {assign var="openid" value=$openid|default:''}

  {if $allowFakeUsers}
    {include file="bits/fakeUser.tpl"}
  {/if}

  <h3>Autentificare cu OpenID</h3>

  <form class="form-inline" method="post" action="{$wwwRoot}auth/login">
    <div class="form-group">
      <label>
        OpenID:
        <input class="form-control" type="text" name="openid" value="{$openid}" size="50" autofocus/>
      </label>
    </div>
    <button class="btn btn-primary" type="submit">
      Autentificare
    </button>
  </form>

  <div class="voffset3"></div>

  <p>
    Dacă aveți un cont Google sau Yahoo, îl puteți folosi ca OpenID:
  </p>

  <div>
    <a href="{$wwwRoot}auth/login?openid=google">
      <img src="{$imgRoot}/openid/btn_google_dark_normal.png" alt="Autentificare cu un cont Google">
    </a>
    <a href="{$wwwRoot}auth/login?openid=yahoo">
      <img src="{$imgRoot}/openid/btn_yahoo_light.png" alt="Autentificare cu un cont Yahoo">
    </a>
  </div>

  <h3>Ce este OpenID?</h3>

  <div>
    <img src="{$imgRoot}/openid/openid.png" alt="Logo OpenID"/>

    <span>este o modalitate mai rapidă și mai ușoară de a vă autentifica pe un site web.</span>
  </div>

  <ul>
    <li>Nu este nevoie să vă creați un cont nou pentru <i>dexonline</i>, ceea ce vă economisește timp;</li>
    <li>Nu este nevoie să memorați o parolă în plus;</li>
    <li>Un cont OpenID, odată creat, poate fi refolosit pe orice site care admite OpenID, iar numărul acestora este în creștere;</li>
    <li>Sunt șanse mari să aveți deja un OpenID, deoarece multe site-uri mari (Google, Yahoo și altele) servesc și ca furnizori de OpenID;</li>
    <li>Dacă aveați deja un cont pe <i>dexonline</i>, îl veți putea revendica și asocia cu OpenID-ul dumneavoastră.</li>
  </ul>

  <p>
    Puteți citi mai multe informații pe <a href="http://openid.net/">site-ul OpenID</a>
    (în limba engleză).
  </p>

  <h3>Cum obțin un OpenID?</h3>

  <p>
    Vizitați <a href="http://openid.net/get-an-openid/">lista furnizorilor de OpenID</a>.
  </p>

  <h3>Precizare</h3>

  <p>
    Majoritatea funcțiilor din <i>dexonline</i> nu necesită autentificarea, cu excepțiile:
  </p>

  <ul>
    <li>Dacă contribuiți cu definiții, ele se vor adăuga în contul dumneavostră;</li>
    <li>Vă puteți crea o listă de definiții favorite pentru acces ușor.</li>
  </ul>
{/block}
