{extends "layout-admin.tpl"}

{block "title"}Editează abrevieri pentru dicționar{/block}

{block "content"}
  <h3>Editează abrevieri pentru dicționar</h3>

  {notice type=warning}
    În momentul editării unei abrevieri, prin schimbarea formei acesteia, sunt
    afectate și celelalte definiții deja moderate din acel dicționar.
  {/notice}

  <div class="card mb-3">
    <div class="card-header">
      Alegere sursă
    </div>
    <div class="card-body">
      <form class="d-flex align-items-center mb-3">
        <label class="me-2">sursa</label>
        {include "bits/sourceDropDown.tpl" sources=$allSources skipAnySource=true}
        <button type="button" class="btn btn-primary ms-2" id="load">
          afișează
        </button>
      </form>

      <h5>Explicații pentru capul de tabel</h5>

      <dl class="row">
        <dt class="col-sm-3">Imp. = abreviere impusă</dt>
        <dd class="col-sm-9">
          Nu ia în considerare forma editată și impune forma din câmpul „Abreviere”.
          <div class="text-muted">
            Folositoare în cazul unui OCR inexact sau inconsistență în dicționar
          </div>
        </dd>

        <dt class="col-sm-3">Amb. = abreviere ambiguă</dt>
        <dd class="col-sm-9">
          Pentru situații similare cu <em>loc., ac., cont.</em>
        </dd>

        <dt class="col-sm-3">CS = case sensitive</dt>
        <dd class="col-sm-9">
          Diferențiere între majuscule și minuscule: v. ≠ V.
        </dd>

        <dt class="col-sm-3">Abreviere</dt>
        <dd class="col-sm-9">
          permite și alte semne de punctuație (nu doar <code>.</code>) și formatare internă
          <code>$</code>, <code>@</code>, <code>%</code>, <code>_{}</code>, <code>^{}</code>
        </dd>

        <dt class="col-sm-3">Detalierea abrevierii</dt>
        <dd class="col-sm-9">
          permite formatare internă <code>$</code>, <code>@</code>, <code>%</code>,
          <code>_{}</code>, <code>^{}</code>
        </dd>
      </dl>

    </div>
  </div>

  {* div populated by ajax calls *}
  <div id="abbrevs"></div>
  {include "bits/abbrevListModal.tpl"}
{/block}
