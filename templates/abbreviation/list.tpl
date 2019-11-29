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
        <div class="form-group">
          <label class="col-md-1 control-label">sursa</label>
          <div class="col-md-11">
            <div class="input-group">
              {include "bits/sourceDropdown.tpl" id=$sources.vars.id}
              <span id="load" class="input-group-addon ld-ext-left" data-toggle="collapse" 
                  data-target="#loadWarning" role="button" aria-expanded="true" aria-controls="loadWarning">
                <b id="count">0</b>
                <div class="ld ld-ring ld-spin-fast"></div>
              </span>
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-3">
              <p class="text-muted">
                Explicații pentru capul de tabel
                <i class="glyphicon glyphicon-question-sign" data-toggle="collapse" 
                  data-target="#tableHeader" role="button" aria-expanded="true" aria-controls="tableHeader">
                </i>
              </p>
            </div>
            <div id="loadWarning" class="col-md-9 collapse">
              <span class="text-muted pull-right">
                aveți răbdare până se formatează tabelul
              </span>
            </div>
          </div>
          <div id="tableHeader" class="container-fluid collapse">
            <ul class="col-sm-11">
              <li> Imp. - abreviere impusă - nu ia în considerare forma editată
                și impune forma din câmpul „Abreviere” -
                <i>folositoare în cazul unui OCR inexact sau inconsecvență în dicționar</i>
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
