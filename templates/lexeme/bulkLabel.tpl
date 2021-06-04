{extends "layout-admin.tpl"}

{block "title"}Etichetare sufix -{$suffix}{/block}

{block "content"}
  <h3>Etichetare sufix -{$suffix}</h3>

  <p>
    <a class="btn btn-link" href="{Router::link('lexeme/bulkLabelSelectSuffix')}">
      {include "bits/icon.tpl" i=arrow_back}
      înapoi la lista de sufixe
    </a>
  </p>

  <div class="alert alert-info alert-dismissible" role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

    <ul class="mb-0">
      <li>Sunt prezentate maximum 20 de lexeme pe pagină.</li>
      <li>
        Restricțiile nu sunt luate în considerare în timp real (toate
        formele vor fi afișate chiar dacă indicați unele restricții),
        dar vor fi procesate corect când trimiteți formularul.
      </li>
      <li>
        Dacă ignorați un lexem, el nu va fi modificat și va continua să apară în listă.
      </li>
    </ul>
  </div>

  <form class="form-horizontal" method="post">
    <input type="hidden" name="suffix" value="{$suffix|escape}">
    {foreach $lexemes as $lIter => $l}
      <div class="card mb-3">

        <div class="card-header">
          {$lIter+1}. {$l->formNoAccent|escape}
          <a href="{Router::link('lexeme/edit')}?lexemeId={$l->id}">
            {include "bits/icon.tpl" i=edit}
            editează
          </a>
        </div>

        <div class="card-body">
          <div class="row mb-2">
            {** Radio buttons to choose the model. **}
            <label class="col-md-2">
              model
            </label>

            <div class="col-md-10">
              {foreach $models as $i => $m}
                {assign var="mId" value="`$m->modelType`_`$m->number`"}
                <div class="form-check form-check-inline">
                  <label class="form-check-label">
                    <input class="form-check-input modelRadio"
                      type="radio"
                      name="lexeme_{$l->id}"
                      value="{$mId}"
                      data-paradigm-id="paradigm_{$lIter}_{$i}">
                    {$m->modelType}{$m->number} ({$m->exponent})
                  </label>
                </div>
              {/foreach}
              <label class="form-check-label">
                <input class="form-check-input modelRadio"
                  type="radio"
                  name="lexeme_{$l->id}"
                  value="0"
                  checked>
                ignoră
              </label>
            </div>
          </div>

          {** Restriction checkboxes, if applicable **}
          <div class="row mb-2">
            <label class="col-md-2 col-form-label">
              restricții
            </label>

            <div class="col-md-10">
              <input type="text" class="form-control" name="restr_{$l->id}">
            </div>
          </div>

          <hr>

          {** Definitions **}
          <div>
            {foreach $searchResults[$lIter] as $row}
              {include "bits/definition.tpl" showDropup=0 showUser=0 showPageLink=false}
            {/foreach}
          </div>

          {** Only one paradigm will be visible at any time. **}
          {assign var="lArray" value=$lMatrix[$lIter]}
          {foreach $lArray as $pIter => $l }
            {assign var="m" value=$models[$pIter]}
            {assign var="mt" value=$modelTypes[$pIter]}
            <div id="paradigm_{$lIter}_{$pIter}" class="paradigm" hidden>
              {include "paradigm/paradigm.tpl" lexeme=$l}
            </div>
          {/foreach}
        </div>
      </div>
    {/foreach}

    <button type="submit" class="btn btn-primary" name="saveButton">
      {include "bits/icon.tpl" i=save}
      <u>s</u>alvează
    </button>
  </form>
{/block}
