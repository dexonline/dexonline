<?php /* Smarty version 2.6.18, created on 2008-01-23 13:06:36
         compiled from common/tools.ihtml */ ?>
<h4>Clienţi off-line</h4>

Aceste aplicaţii transferă definiţiile din <i>DEX online</i> pe
calculatorul dumneavoastră personal. Ulterior, le puteţi consulta şi
în absenţa unei conexiuni la internet. <i>DEXter</i> şi <i>DEX
Offline</i> sunt momentan nefuncţionale, deoarece protocolul prin
care <i>DEX online</i> îşi exportă baza de date s-a schimbat, iar cele
două aplicaţii nu au fost aduse la zi.

<ul>
  <li><a href="http://dixit.sourceforge.net/">Dixit</a> (GPL,
  disponibil pentru Windows sau GNU Linux, autor Octavian
  Procopiuc);</li>

  <li><a href="http://www.lex.md/">DEX.Ro</a> (autor <a
  href="mailto:silvestru@yahoo.com">Ion Silvestru</a>);</li>

  <li><a
  href="http://www.federicomestrone.com/jadex/">JaDEX</a>
  (client Java, portabil pe orice sistem de operare, autor Federico
  Mestrone);</li>

  <li><a href="http://www.tranzactiibursiere.ro/maestro/">Maestro
  DEX</a> (client pentru Windows, cu o interfaţă prietenoasă
  pentru nevăzători, autor Octavian Râşniţă);</li>

  <li><a href="http://pocketdex.aamedia.ro//">PocketDEX</a> (client
  pentru Windows CE, inclusiv Pocket PC, autor Alexandru Mirea);</li>

  <li><a href="http://dapyx-soft.com/~bogdan/dexter.zip">DEXter</a> (2.6
  MB);</li>

  <li><a href="http://dexoffline.sourceforge.net/">DEX Offline</a> (4
  MB). Această interfaţă depinde de alte pachete, deci este posibil să
  aveţi mai mult de instalat.</li>
</ul>

<h4>Designul paginii</h4>

<i>DEX online</i> şi-a schimbat înfăţişarea de câteva ori de-a lungul
anilor. Designul curent este <i>polar,</i> iar vechile designuri sunt
disponibile mai jos. Precizăm că numai designul curent este ţinut la
zi cu cele mai noi funcţii ale <i>DEX online</i>.

<ul>
  <li><a href="index.php?skin=polar">polar</a></li>
  <li><a href="index.php?skin=simple">simple</a></li>
  <li><a href="index.php?skin=slick">slick</a> (autor
      <a href="http://timbru.com/">Gabriel Radic</a>)</li>
  <li><a href="index.php?skin=olimp">Olimp</a> (autor Dan Alexandru)</li>
</ul>

<h4>Integrare în browser</h4>

<ul>
  <li>Adăugaţi <i>DEX online</i> la cutia de căutare din Firefox 2.0
  sau Internet Explorer 7: <a href="#"
  onclick="addProvider('http://dexonline.ro/download/dex.xml'); return
  false;">click aici</a>. Mulţumiri lui Alexandru Lixandru.</li>

  <li>Un alt link, funcţional în Firefox şi Mozilla: <a href="#"
  onclick="addToEngines(); return false">click aici</a>. Mulţumiri
  lui <a href="http://www.mit.edu/~michel">Mihai Ibănescu</a>, care a
  creat cele două fişiere, şi
  lui <a href="http://marius.scurtescu.com/">Marius Scurtescu</a>,
  care ne-a oferit codul JavaScript pentru instalarea printr-un singur
  click.</li>
</ul>

<h4><a name="scrabble">Unelte pentru Federaţia Română de Scrabble</a></h4>

<ul>

  <li><a href="scrabbleLoc.php">Lista Oficială de Cuvinte</a>
  acceptată de Federaţia Română de Scrabble.</li>

  <ul>
    <li><a href="html/locLegend.html">Precizări</a> privind
    notaţiile din listă</li>
  </ul>

  <li><a href="viewModels.php">Lista modelelor de flexiune</a>.</li>
  <li><a href="scrabbleCheckInflection.php">Verificare formă
  flexionară</a> - aflaţi rapid dacă o formă flexionară este în
  LOC.</li>

  <?php if ($this->_tpl_vars['is_flex_moderator']): ?>
    <li><a href="scrabbleUniqueForms.php">Lista formelor unice</a>
    (fără diacritice, accente, dublete, cu lungimi între 2 şi 15
    litere).</li>
  <?php endif; ?>

</ul>
