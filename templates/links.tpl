{extends "layout.tpl"}

{block "title"}{'links'|_|cap}{/block}

{block "content"}
  <p>
    {'This page contains useful language links. These links are not sponsored;
    we consider the useful to our visitors. We are not affiliated with the
    respective sites and institutions.'|_}
  </p>

  <h3>{'Romanian language'|_}</h3>

  <ul>
    <li>
      {'Iorgu Iordan - Alexandru Rosetti <a href="%s">Institute of Linguistics</a>,
      Romanian Academy'|_|sprintf
      :"http://www.lingv.ro/"}
    </li>
    <li>
      <a href="http://www.xn--scriuromnete-wbb074b.net/">ScriuRomânește.net</a>
      &mdash; {'why and how to write using diacritics in the Internet'|_}
    </li>
    <li>
      {'Sorin Paliga &mdash; volumes in PDF format at
      <a href="%s">Academia.edu</a> and <a href="%s">ResearchGate</a>'|_|sprintf
      :"https://unibuc.academia.edu/SPaliga"
      :"https://www.researchgate.net/profile/Sorin_Paliga"}
    </li>
  </ul>

  <h3>{'Other languages'|_}</h3>

  <ul>
    <li>
      {'<a href="%s"">hallo.ro</a> &mdash; bilingual dictionaries between
      Romanian and English, French, Spanish, German, Italian'|_|sprintf
      :"http://hallo.ro/"}
    </li>
  </ul>

  <h3>Scrabble</h3>

  <ul>
    <li>
      {'The <a href="%s">Romanian Scrabble Federation</a>'|_|sprintf
      :"http://www.scrabblero.ro/"}
    </li>
    <li>
      {'<a href="%s">AnaScrab 7</a> &mdash; a seven-letter anagram generator'|_|sprintf
      :"http://www.scrabrom.3x.ro/Anascrab7.html"}
    </li>
    <li>
      {'<a href="%s">Find words</a> &mdash; a helper for crosswords and Scrabble'|_|sprintf
      :"https://play.google.com/store/apps/details?id=com.dance.findwords"}
    </li>
  </ul>

  <h3>{'Programs that use the database of dexonline'|_}</h3>

  <ul>
    <li>
      {'A <a href="%s">game of Fazan</a> for Android devices'|_|sprintf
      :"https://play.google.com/store/apps/details?id=com.kynamar.fazan"}
    </li>
  </ul>
{/block}
