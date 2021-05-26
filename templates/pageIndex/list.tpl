{extends "layout-admin.tpl"}

{block "title"}Editează indecșii de pagină pentru dicționar{/block}

{block "content"}
  <h3>Editează indecșii de pagină pentru dicționar</h3>

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
        <dt class="col-1">Vol.</dt>
        <dd class="col-11">
          volumul în care găsim definiția (implicit 1)
        </dd>

        <dt class="col-1">Pag.</dt>
        <dd class="col-11">
          pagina la care se află definiția
        </dd>

        <dt class="col-1">Cuv.</dt>
        <dd class="col-11">
          primul termen definit de pe pagină
        </dd>

        <dt class="col-1">Nr. def.</dt>
        <dd class="col-11">
          numărul definiției (implicit 0)
        </dd>
      </dl>
    </div>
  </div>

  {* div populated by ajax calls *}
  <div id="pageIndexes"></div>
  {include "bits/pageIndexListModal.tpl"}
{/block}
