<h3 id="proiecte">La ce folosim sumele donate</h3>

<p>
  Avem multe idei pentru a dezvolta site-ul și prea puțin timp să ne ocupăm
  voluntar de ele. Echipa noastră constă din șapte oameni și toți suntem
  voluntari. Aceasta înseamnă că lucrăm cu mare plăcere, dar trecem
  <i>dexonline</i> pe planul doi oricând apar nevoi mai urgente în viețile
  noastre. Donația ta va ajuta <i>Asociația dexonline</i> să dezvolte
  proiectul mai repede. De exemplu:
</p>

{capture "body"}
  <p><strong>Cheltuieli estimate:</strong> 5.000 € - 10.000 € pe lună.</p>

  <p>
    <strong>Motiv:</strong>
    Avem suficiente idei de proiecte software, pe termen scurt și lung,
    pentru a angaja cel puțin un coordonator de proiecte, doi programatori
    cu normă întreagă, iar cu jumătate de normă un inginer de testare
    și respectiv un web designer. De exemplu:
  </p>

  <ul>
    <li>un motor de indexare a unor texte românești de calitate, disponibile pe Internet: literatură, articole etc.;</li>
    <li>exemple alături de fiecare definiție, extrase automat din textele indexate anterior;</li>
    <li>o interfață care găsește cuvinte, în textele indexate anterior, care nu apar în <i>dexonline</i> și le trimite către un lexicograf care să
      le elaboreze definiții;</li>
    <li>confruntarea și unificarea definițiilor pentru același cuvânt, din dicționare diferite, pentru eliminarea redundanței;</li>
    <li>restructurarea definițiilor pentru a separa silabația, pronunția, etimologia, sinonimele și variantele;</li>
    <li>jocuri bazate pe dicționare, din serie cu <a href="{Router::link('games/hangman')}">Spânzurătoarea</a> și <a href="{Router::link('games/mill')}">Moara cuvintelor</a>;</li>
    <li>crearea unei aplicații pentru mobil.</li>
  </ul>
    <p>
    Dar e nevoie de oameni și pentru munca un pic mai plictisitoare: rezolvarea bugurilor, aducerea la zi a versiunilor
      de software și a librăriilor de funcții, optimizarea codului la cererea moderatorilor etc.
    </p>
{/capture}

{include "bits/doneaza-section.tpl"
  id=1
  title="Finanțarea unor noi proiecte software"
  body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong>
    1.000 € - 3.000 € per volum.
  </p>

  <p>
    <strong>Motiv:</strong>
    <em>dexonline</em> conține, în majoritatea sa, dicționare generale. Stăm mai puțin bine cu dicționarele de nișă: juridic, medical,
    informatic, tehnic, de artă, de arhitectură, botanic, biologic etc. Există dicționare valoroase pentru multe din aceste domenii și considerăm că
    ar fi util să cumpărăm dreptul de a le publica.
  </p>

  <p>
    Un aspect interesant este dreptul de a redistribui aceste definiții sub o licență cât mai permisivă (Creative Commons sau GNU GPL). Dorim să
    eliberăm limba, astfel ca oricine să poată descărca și folosi listele de cuvinte și definiții. Așa s-au născut multe proiecte de cercetare și
    site-uri web utile. Acest drept are, însă, un cost suplimentar.
  </p>
{/capture}

{include "bits/doneaza-section.tpl"
  id=2
  title="Achiziționarea unor noi dicționare în format electronic"
  body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong>
    4-5 € / pagină.
  </p>

  <p>
    <strong>Motiv:</strong>
    Evoluția limbii este la fel de importantă ca și forma ei curentă. Dicționarele vechi ne arată cum s-a modificat în timp sensul
    cuvintelor și ne permit să înțelegem mai bine cărți vechi. Importarea unui dicționar vechi constă, în general, din patru pași:
  </p>

  <ul>
    <li>Găsirea și cumpărarea lui. Raritatea și costul variază de la ediție la ediție.</li>

    <li>Scanarea lui (circa 10 ¢ / pagină). Acest pas este obligatoriu, deoarece copiile sunt foarte rare și majoritatea oamenilor care tehnoredactează
      definiții nu au acasă o copie.</li>

    <li>Trecerea printr-un OCR (circa 10 ¢ / pagină). Acest pas extrage textul (cu destul de multe erori) din paginile scanate.</li>

    <li>Tehnoredactarea definițiilor și corectarea erorilor de OCR (circa 3-5 € / pagină). Tehnoredactarea este o muncă migăloasă deoarece dicționarele
      vechi folosesc semne grafice și forme ale cuvintelor ieșite din uz, dar noi dorim să le preluăm cu exactitate.</li>
  </ul>
{/capture}

{include "bits/doneaza-section.tpl"
  id=3
  title="Importarea unor dicționare vechi, de referință"
  body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong>
    20-50 € per articol lingvistic; variabile pentru elaborarea de definiții.
  </p>

  <p>
    <strong>Motiv:</strong>
    Dorim să ne îmbogățim <a href="{Router::link('article/list')}">colecția de articole</a>
    pe teme lingvistice (exprimare, pronunție, împrumuturi din alte limbi etc.). Uneori putem
    scrie singuri câteva paragrafe, dar, pentru a nu rămâne la un nivel de diletanți, avem nevoie
    de colaborări cu profesioniști în domeniu.
  </p>

  <p>
    Al doilea motiv este că limba evoluează. Apar mereu cuvinte noi, pe care noi le putem depista în mod automat, prin scanarea periodică a
    site-urilor românești. Nu putem însă să le elaborăm o definiție completă – acesta este apanajul unui lexicograf profesionist.
  </p>

  <p>
    Nu în ultimul rând, <i>dexonline</i> are informații sărace pentru etimologiile multor cuvinte. Dorim să colaborăm cu un etimolog de profesie
    pentru a ne completa informațiile.
  </p>
{/capture}

{include "bits/doneaza-section.tpl"
  id=4
  title="Colaborări plătite cu lexicografi și/sau lingviști"
  body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong>
    variabile în funcție de proiect.
  </p>

  <p>
    <strong>Motiv:</strong>
    Cu vorba bună și cu un apel la conștiința civică am ajuns departe, dar un contract plătit este un stimulent în plus. De exemplu,
    structurarea definițiilor și eliminarea redundanței dintre diversele dicționare sunt proiecte utile care, în bună parte, nu necesită pregătiri
    speciale, ci doar un efort susținut.
  </p>
{/capture}

{include "bits/doneaza-section.tpl"
  id=5
  title="Motivarea comunității de voluntari pentru implicare"
  body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong> 500 - 1.000 € / lună.
  </p>

  <p>
    <strong>Motiv:</strong>
    Lumea iubește concursurile de aptitudini, iar cele lingvistice nu fac excepție. E nevoie de un documentarist care să
    descopere lucruri interesante, de un creator de conținut pentru a le da o formă, de un desenator pentru a le ilustra
    la nevoie și chiar de un programator care să ajute când e necesar. Pentru <i>dexonline,</i> organizarea de concursuri
    ar fi un prilej bun de a stimula oamenii să învețe lucruri noi și de a-i ține aproape de site.
    De exemplu, <a href="{Router::link('wotd/view')}">cuvântul zilei</a> este ales în fiecare zi în legătură cu un
    eveniment notabil din acea zi (în anul curent sau în anii trecuți). Tu câte din aceste  evenimente poți identifica?
  </p>
{/capture}

{include "bits/doneaza-section.tpl"
  id=6
  title="Organizarea de concursuri și oferirea de premii"
  body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong>
    3.000 - 5.000 € / lună.</p>
  <p>
    <strong>Motiv:</strong>
    Spre deosebire de alte site-uri cu un trafic comparabil cu al nostru, <i>dexonline</i> nu deține o fermă de servere.
    Tot proiectul rulează pe două calculatoare, din care stoarcem tot ce putem pentru a face față traficului. Chiar și
    așa, în permanență sunt necesare mici lucrări pentru a ține la zi multitudinea de componente aferente proiectului:
  </p>

  <ul>
    <li>sistemul de operare;</li>
    <li>limbajul de programare;</li>
    <li>actualizarea bibliotecilor de funcții și a frameworkurilor folosite;</li>
    <li>baza de date;</li>
    <li>cache-ul Varnish, pe care îl folosim pentru a accelera servirea paginilor, și care trebuie foarte bine adaptat la nevoile noastre;</li>
    <li>sistemul OTRS, folosit pentru a răspunde la e-mailuri;</li>
    <li>filtrul de spam;</li>
    <li>sistemul de bannere;</li>
    <li>înteținerea blogului;</li>
    <li>pagina wiki.</li>
  </ul>

  <p>Toate adunate pot ține un inginer de sistem ocupat cel puțin două-trei zile pe săptămână. Plus partea de securitate.
    Mai adăugăm și partea de administrator de baze de date și e de muncă toată săptămâna!
  </p>
{/capture}

{include "bits/doneaza-section.tpl"
  id=7
  title="Administrarea și mentenanța sistemului"
  body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong> 500 - 1.000 € / lună.
  </p>

  <p>
    <strong>Motiv:</strong>
    Am putea să ne închidem și noi într-un turn de fildeș și să limităm contactul cu utilizatorii noștri, dar ar fi un
    pic absurd să creăm acest sistem pentru ei și să nu ne intereseze să comunicăm cu ei. Avem de răspuns la e-mailuri,
    de scris articole de blog, de scris newsletterul, de răspuns la comentarii și întrebări. Încercăm să democratizăm
    dialogul cu utilizatorii, așa că existăm și pe rețelele de socializare (facebook și instagram).
    Scopul nostru e să atragem lumea către cunoaștere și știință și fără popularizare nu se poate!
  </p>
{/capture}

{include "bits/doneaza-section.tpl"
id=8
title="Menținerea contactului cu utilizatorii"
body=$smarty.capture.body}

{capture "body"}
  <p>
    <strong>Cheltuieli estimate:</strong> 200 - 500 € / lună.
  </p>

  <p>
    <strong>Motiv:</strong>
    Chiar dacă suntem ingineri (sau poate tocmai de aceea), nu putem reinventa roate. Uneori e pur și simplu mai ieftin
    să cumpărăm soft gata făcut: nu ar avea sens să creăm de la zero un sistem OCR sau să pierdem timp cu crearea unui
    sistem de trimitere a newsletterului. Nu e rentabil economic, nici inginerește nu are sens!
  </p>
{/capture}

{include "bits/doneaza-section.tpl"
id=9
title="Achiziția de software și servicii"
body=$smarty.capture.body}

{capture "body"}
<p>
  <strong>Cheltuieli estimate:</strong> 200 - 500 € / lună.
</p>

<p>
  <strong>Motiv:</strong>
  Cu traficul pe care îl avem trebuie să cumpărăm găzduire, nu mai putem să ținem serverul în sufragerie, ca la început.
  Deocamdată reușim să ținem traficul cu doar două servere, dar suntem la limită! Și dacă plătim facturi, ne trebuie și
  un contabil! Din când în când e nevoie și de avocat (de exemplu pentru menținerea mărcii înregistrate).
</p>
{/capture}

{include "bits/doneaza-section.tpl"
id=8
title="Cheltuieli de infrastructură și administrare"
body=$smarty.capture.body}
