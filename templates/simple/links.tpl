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
    <li>
      <a href="http://www.xn--scriuromnete-wbb074b.net/">ScriuRomânește.net</a>
      &mdash; {t}why and how to write using diacritics on the Internet{/t}
    </li>
    <li>
      {t
        1="https://unibuc.academia.edu/SPaliga"
        2="https://www.researchgate.net/profile/Sorin_Paliga"}
      Sorin Paliga &mdash; volumes in PDF format at <a href="%1">Academia.edu</a>
      and <a href="%2">ResearchGate</a>{/t}
    </li>
  </ul>

  <h3>{t}Other languages{/t}</h3>

  <ul>
    <li>
      {t 1="http://hallo.ro/"}
      <a href="%1">hallo.ro</a> &mdash; bilingual dictionaries between
      Romanian and English, French, Spanish, German, Italian{/t}
    </li>
  </ul>

  <h3>Scrabble</h3>

  <ul>
    <li>
      {t 1="http://www.scrabblero.ro/"}
      The <a href="%1">Romanian Scrabble Federation</a>{/t}
    </li>
    <li>
      {t 1="http://www.scrabrom.3x.ro/Anascrab7.html"}
      <a href="%1">AnaScrab 7</a> &mdash; a seven-letter anagram generator{/t}
    </li>
    <li>
      {t 1="https://play.google.com/store/apps/details?id=com.dance.findwords"}
      <a href="%1">Find words</a> &mdash; a helper for crosswords and Scrabble{/t}
    </li>
  </ul>

  <h3>{t}Programs that use the database of dexonline{/t}</h3>

  <ul>
    <li>
      {t 1="https://play.google.com/store/apps/details?id=com.kynamar.fazan"}
      A <a href="%1">game of Fazan</a> for Android devices{/t}
    </li>
  </ul>
{/block}
