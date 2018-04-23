{extends "layout-admin.tpl"}

{block "title"}Editează abrevieri pentru dicționar{/block}


{block "content"}
  <h3>Editează abrevieri pentru dicționar</h3>

  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <div id="lds-dual-ring" class="pull-right"></div>
      Alegere sursă
    </div>
    
    <div class="panel-body">
      <div class="row pull-right">
        <div class="col-sm-2">
          <div class="btn btn-primary" id="load">
            afișează abrevierile
          </div>
        </div>
        
      </div>
      <div class="row">
        <label class="col-sm-1 control-label align-bottom">sursa</label>
        <div class="col-sm-9">
          {include "bits/sourceDropDown.tpl" sources=$allSources skipAnySource=true}
        </div>
      </div>
      <p class="text-muted">
        Explicații pentru capul de tabel<br />
      </p>
      <ol class="col-sm-11"> 
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
          <i>permite și alte semne de punctuație, nu doar „.” + formatare internă $,@,%,_{},^{}</i>
        </li>
        <li> Detalierea abrevierii - 
          <i>permite formatare internă html $,@,%,_{},^{}</i>
        </li>
      </ol>
    </div>
  </div>
  {* div populated by ajax calls *}
  <div id="abbrevs"></div>
  {include "bits/abbrevEditModal.tpl"}
{/block}
