{extends "layout-admin.tpl"}

{block "title"}Editează indexul de pagini pentru dicționar{/block}

{block "content"}
  <h3>Editează indexul de pagini pentru dicționar</h3>

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
              <li> Vol. - volumul dicționarului  -
                <i>în cazul în care există mai multe volume</i>
              </li>
              <li> Pag. - 
                <i>pagina la care este glosat cuvântul</i>
              </li>
              <li> Cuv. -
                <i>primul cuvânt glosat pe pagină</i>
              </li>
              <li> Indice sau exponent -
                <i>pentru omonime/omografe</i>
              </li>
            </ul>
          </div>
        </div>
      </form>
    </div>
  </div>
  {* div populated by ajax calls *}
  <div id="pageindex"></div>
  {include "bits/pageindexModal.tpl"}
{/block}
