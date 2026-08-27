{extends "layout.tpl"}

{block "title"}{cap}{t}links{/t}{/cap}{/block}

{block "content"}
  <p>
    {t}This page contains useful language links. These links are not sponsored;
    we consider them useful to our visitors. We are not affiliated with the
    respective sites and institutions.{/t}
  </p>

  <h3>{t}Romanian language{/t}</h3>

  <ul>
    <li>
      {t 1="http://www.lingv.ro/"}
      Iorgu Iordan - Alexandru Rosetti <a href="%1">Institute of Linguistics</a>,
      Romanian Academy{/t}
    </li>
  </ul>
{*
  <h3>{t}Other languages{/t}</h3>

  <ul>
    <li>
      {t 1="http://hallo.ro/"}
      <a href="%1">hallo.ro</a> &mdash; bilingual dictionaries between
      Romanian and English, French, Spanish, German, Italian{/t}
    </li>
  </ul>
*}
  <h3>{cap}{t}games{/t}{/cap}</h3>

  <ul>
    <li>
      {t 1="http://www.scrabblero.ro/"}
      The <a href="%1">Romanian Scrabble Federation</a>{/t}
    </li>
  </ul>
{/block}
