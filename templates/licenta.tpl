{extends file="layout.tpl"}

{block name=title}Licență{/block}

{block name=content}
  <h2>Licență</h2>

  <p class="paragraphTitle">Pentru dicționare fără drept de redistribuire</p>

  Unele dintre dicționarele prezentate sunt produse din fonduri private și nu avem acordul pentru a le redistribui. Copierea definițiilor din aceste
  dicționare este interzisă fără acordul explicit al deținătorilor de drepturi:
  <br/><br/>

  <table class="licences minimalistTable">
    <tr>
      <th>Nume</th>
      <th>Autor</th>
      <th>Editură</th>
    </tr>
    {foreach from=$restrictedSources item=rs}
      <tr>
        <td class="dictionaryName">{$rs->name}<span class="sourceShortName">{$rs->shortName}</span></td>
        <td class="author">{$rs->author}</td>
        <td>{$rs->publisher}</td>
      </tr>
    {/foreach}
  </table>
  <br/>

  <p class="paragraphTitle">Pentru restul dicționarelor</p>

  Copyright (C) 2004-{$currentYear} dexonline
  <br/><br/>

  Baza de definiții a <i>dexonline</i> este liberă; o puteți redistribui și/sau modifica în conformitate cu termenii Licenței Publice Generale GNU așa
  cum este ea publicată de Free Software Foundation; fie versiunea 2 a Licenței, fie (la latitudinea dumneavoastră) orice versiune ulterioară.  Baza de
  definiții este distribuită cu speranța că vă va fi utilă, dar FĂRĂ NICIO GARANȚIE, chiar fără garanția implicită de vandabilitate sau conformitate
  unui anumit scop. Citiți Licența Publică Generală GNU pentru detalii.  Puteți găsi o copie a Licenței Publice Generale GNU <a
href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html">aici</a> și o traducere a ei în limba română
  <a href="https://ro.wikipedia.org/wiki/GPL_%28licen%C8%9B%C4%83,_versiunea_2%29">aici</a>.

  <p class="paragraphTitle">Pentru codul-sursă</p>

  Copyright (C) 2004-{$currentYear} dexonline
  <br/><br/>

  Acest program este software liber: îl puteți redistribui și/sau modifica sub termenii <a href="http://www.gnu.org/licenses/agpl-3.0.html">Licenței
    Publice Generale Affero (AGPL)</a> așa cum este ea publicată de Free Software Foundation, fie versiunea 3 a licenței, fie (la latitudinea
  dumneavoastră) orice versiune ulterioară. Acest program este distribuit cu speranța că vă va fi util, dar FĂRĂ NICIO GARANȚIE, chiar fără garanția
  implicită de vandabilitate sau conformitate unui anumit scop. Citiți Licența Publică Generală Affero pentru detalii.
  <br/><br/>

  Pentru instrucțiuni privind descărcarea și instalarea codului-sursă, vizitați pagina <a
href="http://wiki.dexonline.ro/wiki/Instruc%C8%9Biuni_de_instalare">Acces la codul-sursă</a>.

  <p class="paragraphTitle">Explicație</p>

  Am pus această licență pe definițiile existente în <i>dexonline</i> pentru a ne asuma paternitatea asupra formei digitale a definițiilor. <b>Nu</b>
  dorim să ne substituim autorilor originali ai dicționarelor incluse. Precizăm din nou că <i>dexonline</i> este doar o copie online a DEX, ediția 1998
  și a altor dicționare asupra cărora nu avem niciun fel de pretenții de paternitate. Este posibil ca, în urma unor convorbiri ulterioare cu Academia
  Română, această licență să devină invalidă. Este posibil chiar ca, din motive legale, să fim nevoiți a revizui conținutul acestui site. Deocamdată,
  însă, aceste convorbiri nu au avut loc. Cât timp lucrurile vor rămâne în această stare, dorim ca nimeni să nu poată confisca sau îngrădi accesul la
  baza de date a <i>dexonline</i>.
  <br/><br/>

  În cazul în care doriți să preluați definiții din <i>dexonline</i>, licența GPL vă obligă la două lucruri:

  <ol>
    <li>Să oferiți mai departe aceste definiții tot sub licența GPL.</li>
    <li>Să includeți nota de copyright aflată la sfârșitul fiecărei pagini din <i>dexonline</i>, inclusiv URL-ul https://dexonline.ro.</li>
  </ol>

  Am pus această licență pe definiții:

  <ol>

    <li>Pentru a ne asigura că baza de date a <i>dexonline</i>, care este în prezent cea mai cuprinzătoare colecție online de dicționare ale limbii române, va
      rămâne întotdeauna gratuită. Puteți folosi această bază de definiții în orice scopuri, comerciale sau necomerciale.</li>

    <li>Pentru a ne asigura că oricine va folosi această bază de date va fi la rândul său obligat să o ofere mai departe în mod gratuit. Mai exact,
      oricui folosește această bază de date i se interzice să îngreuneze copierea mai departe a acestei baze de date. Aceasta este intenția <a
  href="http://www.gnu.org/copyleft/">copyleft</a>, în cadrul căruia intră și Licența Publică Generală GNU.</li>

    <li>Pentru a ne asigura că, în cazul în care cineva preia și modifică baza de date a <i>dexonline</i>, noi nu putem fi socotiți
      răspunzători. <i>dexonline</i> își propune să ofere o copie fidelă a multor dicționare, dar nu promitem că această copie va fi distribuită identic
      odată ce iese din mâinile noastre.</li>

    <li>Pentru că suntem de părere că acest document este foarte prețios și este un instrument benefic pentru limba și cultura română. Considerăm că
      acest document trebuie să fie public și că el s-ar răspândi oricum, chiar și fără voia noastră. De aceea, dorim să oferim acest document de la sursă
      și să preîntâmpinăm apariția unei piețe negre pentru acest document.</li>
  </ol>
{/block}
