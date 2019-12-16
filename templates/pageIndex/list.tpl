{extends "layout-admin.tpl"}

{block "title"}Editează indecșii de pagină pentru dicționar{/block}

{block "content"}
  <h3>Editează indecșii de pagină pentru dicționar</h3>

  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      Alegere sursă
    </div>
    <div class="panel-body">
      <form class="form form-horizontal">
        <div class="row">
          <div class="col-sm-1">
            <label class="control-label">sursa</label>
          </div>
          <div class="col-sm-9">
            {include "bits/sourceDropDown.tpl" sources=$allSources skipAnySource=true}
          </div>
          <div class="col-sm-2">
            <button type="button" class="btn btn-primary btn-block ld-ext-left" id="load">
              afișează
              <div class="ld ld-ring ld-spin-fast"></div>
            </button>
          </div>
        </div>
        <div class="container">
          <div class="row">
            <p class="text-muted">
              Explicații pentru capul de tabel<br />
            </p>
          </div>
          <div class="container-fluid">
            <ul class="col-sm-11">
              <li> Vol. – <i>volumul în care găsim definiția (implicit 1)</i>
              </li>
              <li> Pag. – <i>pagina la care se află definiția</i>
              </li>
              <li> Cuv. – <i>primul termen definit de pe pagină</i>
              </li>
              <li> Nr. def. – <i>numărul definiției (implicit 0)</i>
              </li>
            </ul>
          </div>
        </div>
      </form>
    </div>
  </div>
  {* div populated by ajax calls *}
  <div id="pageIndexes"></div>
  {include "bits/pageIndexListModal.tpl"}
{/block}
