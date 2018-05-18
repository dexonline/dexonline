{extends "layout-admin.tpl"}

{block "title"}Examinare abrevieri{/block}

{block "content"}
  <h3>Abrevieri ambigue</h3>

  <form class="form-inline">
    <label>sursa</label>
    <select id="sourceId" name="sourceId" class="form-control">
      <option value="">oricare</option>
      {foreach $sources as $s}
        <option value="{$s->id}" {if $s->id == $sourceId}selected{/if}>
          {$s->shortName}
          ({$s->numAmbiguous})
        </option>
      {/foreach}
    </select>
  </form>

  <div class="voffset3"></div>

  {if $def}
    <form id="reviewForm" method="post">
      <input type="hidden" name="definitionId" value="{$def->id}">
      <input type="hidden" name="actions" value="">

      <p>
        {HtmlConverter::convert($def)}
      </p>

      {include "bits/footnotes.tpl" footnotes=$def->getFootnotes()}

      <div class="form-group">
        <button type="submit"
          class="btn btn-success"
          name="saveButton"
          disabled>
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
    <div class="alert alert-success">
      {if $sourceId}
        Nu există definiții de revizuit din această sursă. Puteți alege altă sursă.
      {else}
        Nu există definiții de revizuit. Ura!
      {/if}
    </div>
  {/if}

  <div class="alert alert-info">
    <p>
      <strong>Precizări:</strong>
      <i>dexonline</i> detectează automat majoritatea abrevierilor. Totuși,
      unele bucăți din text sunt ambigue; <b>lat.</b> poate însemna <i>limba latină</i>,
      dar poate fi și cuvântul <i>lat</i> la sfârșitul unei propoziții. În
      aceeași situație se află <b>gen.</b> (<i>genitiv</i>), <b>dat.</b> (<i>dativ</i>)
      și altele. Aceste cazuri au nevoie de decizia unui operator uman. Vi se
      prezintă o definiție la întâmplare, dintre cele cu ambiguități. Pentru
      fiecare ambiguitate, indicați dacă este o abreviere

      <button
        class="btn btn-primary btn-xs"
        type="button"
        data-abbrev="1"
        title="abreviere">
        <i class="glyphicon glyphicon-chevron-left"></i>
      </button>

      sau un cuvânt propriu-zis

      <button
        class="btn btn-primary btn-xs"
        type="button"
        data-abbrev="0"
        title="cuvânt">
        <i class="glyphicon glyphicon-chevron-right"></i>
      </button>

      . La final, salvați definiția. Sistemul avansează automat la o altă
      definiție aleatorie.
    </p>

    <p>
      Puteți folosi tastele <strong>1</strong> și <strong>2</strong> pentru a
      rezolva următoarea ambiguitate ca abreviere, respectiv ca cuvânt.
    </p>
  </div>

  {* stem to be copied for every span *}
  {strip}
  <div id="stem" class="hide">
    <span class="ambigAbbrev" data-action="">
      <button
        class="btn btn-primary btn-xs"
        type="button"
        data-abbrev="1"
        title="abreviere">
        <i class="glyphicon glyphicon-chevron-left"></i>
      </button>
      <span class="text"></span>
      <button
        class="btn btn-primary btn-xs"
        type="button"
        data-abbrev="0"
        title="cuvânt">
        <i class="glyphicon glyphicon-chevron-right"></i>
      </button>
    </span>
  </div>
  {/strip}
{/block}
