{extends "layout-admin.tpl"}

{block "title"}Unificare lexeme{/block}

{block "content"}
  <h3>Unificare lexeme - {$lexemes|@count} rezultate</h3>

  <div class="alert alert-info alert-dismissible" role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

    Pentru fiecare lexem la plural sunt indicate lexemele la singular
    corespunzătoare. Bifați unul sau mai multe, după caz. Puteți salva
    pagina în orice moment; lexemele rămase vor fi afișate din nou la
    reîncărcarea paginii. Pentru moment, nu există o modalitate de a
    „ignora” un lexem. Lexemele pe care nu le unificați vor apărea mereu
    în listă.
  </div>

  <form class="d-flex">
    <label class="col-form-label">tipul lexemului</label>
    <div class="mx-2">
      <select name="modelType" class="form-select">
        <option value="">Toate (lent)</option>
        <option value="M" {if $modelType == 'M'}selected{/if}>M</option>
        <option value="F" {if $modelType == 'F'}selected{/if}>F</option>
        <option value="N" {if $modelType == 'N'}selected{/if}>N</option>
        <option value="T" {if $modelType == 'T'}selected{/if}>T (lent)</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">
      filtrează
    </button>
  </form>

  <form class="mt-2" method="post">
    <input type="hidden" name="modelType" value="{$modelType}">

    {foreach $lexemes as $lIter => $l}
      <div class="card mb-2">

        <div class="card-header">
          {$lIter+1}. <strong>{$l->form|escape}</strong>
          {$l->modelType}{$l->modelNumber}{$l->restriction}
          <a href="{Router::link('lexeme/edit')}?lexemeId={$l->id}" class="btn btn-link">
            {include "bits/icon.tpl" i=edit}
            editează
          </a>

          <a
            class="btn btn-link"
            data-bs-toggle="collapse"
            href="#def_{$l->id}"
            role="button"
            aria-expanded="false"
            aria-controls="def_{$l->id}">
            {include "bits/icon.tpl" i=description}
            definiții
          </a>
        </div>

        <div class="card-body card-admin">
          <div class="card card-body mb-2 collapse" id="def_{$l->id}">
            {foreach $definitions[$l->id] as $row}
              {include "bits/definition.tpl" showDropup=0 showUser=0}
            {/foreach}
          </div>

          {foreach $l->matches as $match}
            <div class="row px-3 align-items-center">
              {capture "label"}
              Unifică cu <strong>{$match->form}</strong>
              {$match->modelType}{$match->modelNumber}{$match->restriction}
              {/capture}

              {include "bs/checkbox.tpl"
                name="merge_{$l->id}_{$match->id}"
                label=$smarty.capture.label
                divClass='col-3'}

              <a
                href="{Router::link('lexeme/edit')}?lexemeId={$match->id}"
                class="btn btn-link col-2">
                {include "bits/icon.tpl" i=edit}
                editează
              </a>

              <a
                class="btn btn-link col-2"
                data-bs-toggle="collapse"
                href="#def_{$match->id}"
                role="button"
                aria-expanded="false"
                aria-controls="def_{$match->id}">
                {include "bits/icon.tpl" i=description}
                definiții
              </a>
            </div>

            {if $match->lostForms}
              <ul>
                <li>
                  Următoarele forme se vor pierde:
                  {foreach $match->lostForms as $form}
                    {$form}
                  {/foreach}
                </li>
              </ul>
            {/if}

            <div class="card card-body mt-2 collapse" id="def_{$match->id}">
              {foreach $definitions[$match->id] as $row}
                {include "bits/definition.tpl" showDropup=0 showUser=0}
              {/foreach}
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
