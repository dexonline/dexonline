{extends "layout-admin.tpl"}

{block "title"}Editare model{/block}

{block "content"}
  {assign var="adjModels" value=$adjModels|default:null}

  <h3>Editare model {$m->modelType}{$m->number}</h3>

  <form id="modelForm" method="post">
    <input type="hidden" name="id" value="{$m->id}">

    <div class="card mb-3">
      <div class="card-header">
        Proprietăți
      </div>

      <div class="card-body">
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">număr de model</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="number" value="{$m->number|escape}">
            <span class="form-text">poate conține orice caractere</span>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">descriere</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="description" value="{$m->description|escape}">
          </div>
        </div>

        {if $adjModels}
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">model de participiu</label>
            <div class="col-sm-9">
              <select class="form-select" name="participleNumber">
                {foreach $adjModels as $am}
                  <option value="{$am->number}"
                    {if $pm && $pm->adjectiveModel == $am->number}selected{/if}
                  >{$am->number} ({$am->exponent})
                  </option>
                {/foreach}
              </select>
            </div>
          </div>
        {/if}

        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">exponent</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="exponent" value="{$m->exponent|escape}">
          </div>
        </div>

      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        Forme
      </div>

      <table class="table table-sm mb-0">
        <tr>
          <th class="row mx-0">
            <div class="col-5">flexiune</div>
            <div class="col-1"></div>
            <div class="col-6 row">
              <div class="col-8">forme</div>
              <div class="col-2">recom</div>
              <div class="col-2">apocopă</div>
            </div>
          </th>
        </tr>

        {foreach $forms as $inflId => $f}
          <tr>
            <td class="row mx-0">
              <div class="col-5">
                {$inflectionMap[$inflId]->description|escape}
              </div>
              <div class="col-1 addFormLink" data-infl-id="{$inflId}">
                <a href="#">
                  {include "bits/icon.tpl" i=add}
                </a>
              </div>
              <div class="col-6">
                {foreach $f as $i => $tuple}
                  <div class="fieldWrapper row mb-1">
                    <div class="col-8">
                      <input class="form-control form-control-sm"
                        type="text"
                        name="forms_{$inflId}_{$i}"
                        value="{$tuple.form|escape}">
                    </div>
                    <div class="col-2">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        name="recommended_{$inflId}_{$i}"
                        value="1"
                        {if $tuple.recommended}checked{/if}>
                    </div>
                    <div class="col-2">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        name="hasApocope_{$inflId}_{$i}"
                        value="1"
                        {if $tuple.hasApocope}checked{/if}>
                    </div>
                  </div>
                {/foreach}
              </div>
            </td>
          </tr>
        {/foreach}
      </table>
    </div>

    <div>
      <button class="btn btn-primary" type="submit" name="saveButton">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>

      <a href="?id={$m->id}" class="btn btn-link">
        renunță
      </a>
    </div>

    <div class="alert alert-warning mt-3">
      Lexemele nu mai sunt salvate imediat, ci vor apărea în
      <a class="alert-link" href="{Router::link('report/staleParadigms')}">
        raportul de paradigme învechite</a>.
      Dacă în model există erori care fac imposibilă regenerarea paradigmei,
      veți primi acele erori cînd încercați regenerarea paradigmei din raport.
    </div>

  </form>
{/block}
