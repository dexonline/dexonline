{extends "layout.tpl"}

{block "title"}Recuperarea parolei{/block}

{block "search"}{/block}

{block "content"}
  <p>
    Am trimis la adresa <b>{$email}</b> un e-mail cu instrucțiuni pentru
    recuperarea parolei. În mod normal, îl vei primi în cîteva minute. Nu uita
    să verifici și folderul de spam, în eventualitatea în care mesajul ajunge
    acolo. Codul de recuperare este activ 24 de ore; te rugăm să îl folosești
    până atunci.
  </p>

  <p>
    Apoi, poți reveni la <a href="{Router::link('auth/login', true)}">pagina de autentificare</a>.
  </p>
{/block}
