{extends file="layout-admin.tpl"}

{block name=title}Plasare accente{/block}

{block name=content}
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
    {foreach $lexems as $l}
      {assign var=lexemId value=$l->id}
      {assign var=charArray value=$chars[$lexemId]}
      {assign var=srArray value=$searchResults[$lexemId]}

      <div>
        <input type="hidden" name="position_{$l->id}" value="-1"/>

        <span class="apLexemForm">
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
          <a class="btn btn-link" href="lexemEdit.php?lexemId={$l->id}">
            <i class="glyphicon glyphicon-pencil"></i>
            editează
          </a>
        </span>
      </div>

      <div class="panel-admin">
        {foreach $srArray as $row}
          <div class="defWrapper">
            <p class="def">{$row->definition->htmlRep}</p>
            <p class="defDetails">
              sursa: {$row->source->shortName|escape} |
              starea: {$row->definition->getStatusName()}
            </p>
          </div>
        {/foreach}
      </div>
    {/foreach}

    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>
  </form>
{/block}
