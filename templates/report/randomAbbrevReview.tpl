{extends "layout-admin.tpl"}

{block "title"}Examinare abrevieri{/block}

{block "content"}
  <h3>Abrevieri ambigue</h3>

  <form class="row row-cols-sm-auto g-2 align-items-center mb-4">
    <div class="col-12">
      <label class="col-form-label">sursa</label>
    </div>
    <div class="col-12">
      <select id="sourceId" name="sourceId" class="form-select">
        <option value="">oricare</option>
        {foreach $sources as $s}
          <option value="{$s->id}" {if $s->id == $sourceId}selected{/if}>
            {$s->shortName}
            ({$s->numAmbiguous})
          </option>
        {/foreach}
      </select>
    </div>
  </form>

  {if $def}
    <form id="reviewForm" method="post">
      <input type="hidden" name="definitionId" value="{$def->id}">
      <input type="hidden" name="actions" value="">

      <p>
        {HtmlConverter::convert($def)}
      </p>

      {include "bits/footnotes.tpl" footnotes=$def->getFootnotes()}

      <div>
        <button type="submit"
          class="btn btn-primary"
          name="saveButton"
          disabled>
          {include "bits/icon.tpl" i=save}
          <u>s</u>alvează
        </button>

        <a class="btn btn-link" href="{Router::link('definition/edit')}/{$def->id}">
          {include "bits/icon.tpl" i=edit}
          editează
        </a>
      </div>
    </form>
  {else}
    {notice type="success"}
      {if $sourceId}
        Nu există definiții de revizuit din această sursă. Puteți alege altă sursă.
      {else}
        Nu există definiții de revizuit. Ura!
      {/if}
    {/notice}
  {/if}

  {notice type="info" class="mt-3"}
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
        class="btn btn-primary btn-sm p-0"
        type="button"
        data-abbrev="1"
        title="abreviere">
        {include "bits/icon.tpl" i=chevron_left}
      </button>

      sau un cuvânt propriu-zis

      <button
        class="btn btn-primary btn-sm p-0"
        type="button"
        data-abbrev="0"
        title="cuvânt">
        {include "bits/icon.tpl" i=chevron_right}
      </button>

      . La final, salvați definiția. Sistemul avansează automat la o altă
      definiție aleatorie.
    </p>

    <p class="mb-0">
      Puteți folosi tastele <strong>1</strong> și <strong>2</strong> pentru a
      rezolva următoarea ambiguitate ca abreviere, respectiv ca cuvânt.
    </p>
  {/notice}

  {* stem to be copied for every span *}
  {strip}
  <div id="stem" class="hide">
    <span class="ambigAbbrev" data-action="">
      <button
        class="btn btn-primary btn-sm p-0"
        type="button"
        data-abbrev="1"
        title="abreviere">
        {include "bits/icon.tpl" i=chevron_left}
      </button>
      <span class="text"></span>
      <button
        class="btn btn-primary btn-sm p-0"
        type="button"
        data-abbrev="0"
        title="cuvânt">
        {include "bits/icon.tpl" i=chevron_right}
      </button>
    </span>
  </div>
  {/strip}
{/block}
