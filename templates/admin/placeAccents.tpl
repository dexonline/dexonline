{extends "layout-admin.tpl"}

{block "title"}Plasare accente{/block}

{block "content"}
  <h3>Plasare accente</h3>

  <div class="alert alert-info alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">
      <span aria-hidden="true">&times;</span>
    </button>

    Dați clic pe litera care trebuie accentuată sau bifați „Nu
    necesită accent” pentru lexemele care nu necesită accent (cuvinte
    compuse, cuvinte latinești etc.). Lexemele sunt alese la
    întâmplare dintre toate cele neaccentuate. Dacă nu știți ce să
    faceți cu un lexem, săriți-l (nu bifați nimic).
  </div>


  <form action="placeAccents.php" method="post">
    {foreach $lexemes as $l}
      {assign var=lexemeId value=$l->id}
      {assign var=charArray value=$chars[$lexemeId]}
      {assign var=srArray value=$searchResults[$lexemeId]}

      <div>
        <input type="hidden" name="position_{$l->id}" value="-1">

        <span class="apLexemeForm">
          {foreach $charArray as $cIndex => $char}
            <span class="apLetter" data-order="{$cIndex}">{$char}</span>
          {/foreach}
        </span>

        <span>
          <label>
            <input type="checkbox"
                   name="noAccent_{$l->id}" value="X">
            Nu necesită accent
          </label>
        </span>

        <span>
          <a class="btn btn-link apDefLink" href="#">
            <i class="glyphicon glyphicon-book"></i>
            <span data-other-text="ascunde definițiile">arată definițiile</span>
          </a>
          <a class="btn btn-link" href="lexemEdit.php?lexemeId={$l->id}">
            <i class="glyphicon glyphicon-pencil"></i>
            editează
          </a>
        </span>
      </div>

      <div class="panel-admin">
        {foreach $srArray as $row}
          {include "bits/definition.tpl" showDropup=0 showId=0 showStatus=1 showUser=0}
        {/foreach}
      </div>
    {/foreach}

    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>
  </form>
{/block}
