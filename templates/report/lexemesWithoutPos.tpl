{extends "layout-admin.tpl"}

{block "title"}Lexeme fără accent{/block}

{block "content"}

  <h3>{$lexemes|count} lexeme fără etichetă de parte de vorbire</h3>

  {include "bits/lexemeList.tpl"}

  <h4 class="mt-3">Detalii</h4>

  <p>
    Pentru o bună prezentare a filei sinteză, dorim ca toate lexemele principale
    asociate cu intrări structurate (structurare terminată sau așteaptă
    moderarea) să aibă cel puțin o etichetă care indică partea de vorbire. În
    prezent, aceste etichete sunt:
  </p>

  <div class="my-3">
    {foreach $tags as $t}
      {include "bits/tag.tpl" link=true}
    {/foreach}
  </div>

  <p>
    Puteți adăuga și scoate etichete din acest set modificând bifa „parte de
    vorbire” în pagina de editare a fiecărei etichete.
  </p>

{/block}
