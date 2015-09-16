{extends file="layout.tpl"}

{block name=title}Contact{/block}

{block name=content}
  Înainte de a ne trimite un e-mail, vă rugăm citiți pagina de <a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii">informații</a>
  pentru a vă asigura că întrebarea dumneavoastră nu este deja tratată acolo. În special:

  <ul>
    <li>Dacă vreți să aflați sensul unui cuvânt care lipsește din <i>dexonline,</i>
      probabil nu vă putem ajuta. Niciunul din dicționarele
      pe care le oferim nu conține acel cuvânt, iar voluntarii <i>dexonline</i>
      nu sunt lingviști de profesie.</li>

    <li>Dacă doriți să ne propuneți un schimb de link-uri, nu ne
      interesează. <i>dexonline</i> a ajuns acolo unde este prin meritul
      propriu și fiecare link către noi vine de la o persoană care ne-a
      considerat utili. Acesta este principiul democrației pe
      web. Schimburile artificiale de link-uri sunt împotriva acestei
      democrații.</li>

    <li>Dacă doriți să instalați <i>dexonline</i> pe calculatorul
      dumneavoastră personal, vizitați pagina de <a href="unelte">unelte</a>.</li>

    <li>Dacă doriți o copie a bazei de date, citiți secțiunea <a
  href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii#Desc.C4.83rcare">Descărcare</a>.</li>

    <li>Dacă vreți să ne felicitați, scrieți-ne neapărat! Cuvinte
      frumoase ne face întotdeauna plăcere să auzim :)</li>
  </ul>

  Adresa noastră de e-mail este
  <a href="mailto:{$cfg.global.contact|escape}">{$cfg.global.contact|escape}</a>. Vă așteptăm!
{/block}
