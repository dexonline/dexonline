{extends file="layout-admin.tpl"}

{block name=title}Examinare abrevieri{/block}

{block name=content}
  <h3>Examinare abrevieri</h3>

  {assign var=ambiguities value=$ambiguities|default:null}
  <span id="numAmbiguities" style="display: none">{$ambiguities|@count}</span>

  {if $def}
    <form action="" method="post">
      <input type="hidden" name="definitionId" value="{$def->id}"/>

      <p>
        {foreach $text as $i => $chunk name=iter}
          <span>
            {$chunk}
            {if !$smarty.foreach.iter.last}
              <hr>
              <input type="radio" id="radio_{$i}_a" name="radio_{$i}" value="abbrev" style="display: none"/>
              <label for="radio_{$i}_a" data-order="{$i}" data-answer="0">
                <i class="glyphicon glyphicon-zoom-out text-danger"></i>
              </label>
              <span id="abrevText_{$i}" data-clicked="">{$ambiguities[$i]}</span>
              <input type="radio" id="radio_{$i}_w" name="radio_{$i}" value="word" style="display: none"/>
              <label for="radio_{$i}_w" data-order="{$i}" data-answer="1">
                <i class="glyphicon glyphicon-zoom-in text-success"></i>
              </label>
            {/if}
          </span>
        {/foreach}
      </p>

      <div class="form-group">
        <button type="submit"
                class="btn btn-success"
                name="saveButton"
                {if count($ambiguities)}disabled="disabled"{/if}>
          <i class="glyphicon glyphicon-floppy-disk"></i>
          <u>s</u>alvează
        </button>

        <a class="btn btn-link" href="definitionEdit.php?definitionId={$def->id}">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      </div>
    </form>
  {else}
    Nu există definiții de revizuit deocamdată. Ura!<br/>
  {/if}

  <div class="alert alert-info alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">
      <span aria-hidden="true">&times;</span>
    </button>

    <strong>Precizări:</strong>
    <i>dexonline</i> are un sistem care detectează automat abrevierile. Totuși, unele bucăți
    din text sunt ambigue; „lat.” poate însemna „limba latină”, dar poate fi și cuvântul „lat”
    la sfârșitul unei propoziții. În aceeași situație se află „gen.” (genitiv), „dat.” (dativ)
    și altele. Aceste cazuri au nevoie de decizia unui operator uman. Vi se prezintă o definiție
    la întâmplare, dintre cele cu ambiguități. Pentru fiecare ambiguitate, indicați dacă este o
    abreviere (<i class="glyphicon glyphicon-zoom-out text-danger"></i>) sau un cuvânt
    propriu-zis (<i class="glyphicon glyphicon-zoom-in text-success"></i>). Trebuie să
    rezolvați toate ambiguitățile înainte de a putea salva definiția. Sistemul avansează automat
    la o altă definiție aleatoare.
  </div>

    <br/>
{/block}
