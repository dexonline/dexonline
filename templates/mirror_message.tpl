{extends file="layout.tpl"}

{block name=title}Pagină dezactivată{/block}

{block name=content}
  <h3>Pagină dezactivată</h3>

  <p>Există două motive pentru care această pagină ar putea fi dezactivată:</p>

  <ol>
    <li>Acest site al <i>dexonline</i> este doar o copie a
      site-ului principal aflat la <a href="https://dexonline.ro"
                                      target="_blank" >https://dexonline.ro</a>, sau</li>

    <li>Suntem în curs de migrare pe un server nou și am dezactivat
      unele funcții pe durata migrării pentru a nu corupe baza de
      date.</li>

  </ol>

  <p>Următoarele funcții sunt dezactivate:</p>

  <ul>
    <li>Introducerea de noi definiții;</li>
    <li>Semnalarea erorilor;</li>
    <li>Corectarea definițiilor;</li>
    <li>Autentificarea utilizatorilor.</li>
    <li>Accesul ca moderator.</li>
  </ul>

  <p>
    Deocamdată, ne este greu din punct de vedere tehnic să permitem aceste
    operații pe toate copiile site-ului <i>dexonline,</i> deoarece nu avem o
    metodă bună să punem cap la cap modificările făcute în
    locuri diferite. Vă invităm să vizitați site-ul nostru principal
    pentru aceste operații.
  </p>

  <a href="https://dexonline.ro" target="_blank">https://dexonline.ro</a>
  &nbsp;&nbsp;&nbsp;
  <a href="javascript:history.back(1)">Înapoi</a>
{/block}
