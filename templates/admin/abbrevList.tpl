{extends "layout-admin.tpl"}

{block "title"}Editează abrevieri pentru dicționar{/block}

{block "content"}
  <h3>Editează abrevieri pentru dicționar</h3>

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
            <div class="btn btn-primary btn-block ld-ext-left" id="load">
              afișează
              <div class="ld ld-ring ld-spin-fast"></div>
            </div>
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
              <li> Imp. - abreviere impusă - nu ia în considerare forma editată
                și impune forma din câmpul „Abreviere” -
                <i>folositoare în cazul unui OCR inexact sau inconsistență în dicționar</i>
              </li>
              <li> Amb. - abreviere ambiguă  -
                <i>pentru situații similare cu „loc.”, „ac.”, „cont.”</i>
              </li>
              <li> CS - (case sensitive) -
                <i>diferențiere între majuscule și minuscule: v. ≠ V.</i>
              </li>
              <li> Abreviere -
                <i>permite și alte semne de punctuație, nu doar „.” + formatare
                  internă $,@,%,_{},^{}</i>
              </li>
              <li> Detalierea abrevierii -
                <i>permite formatare internă html $,@,%,_{},^{}</i>
              </li>
            </ul>
          </div>
        </div>
      </form>
    </div>
  </div>
  {* div populated by ajax calls *}
  <div id="abbrevs"></div>
  {include "bits/abbrevListModal.tpl"}
{/block}
