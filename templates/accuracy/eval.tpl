{extends "layout-admin.tpl"}

{block "title"}{$project->name} | verificarea acurateței{/block}

{block "content"}
  <h3>Proiect de verificare a acurateței - {$project->name}</h3>

  {if $def}
    <div class="card mb-3">
      <div class="card-header">
        Definiția curentă
        {if !$mine}
          ({$errors} erori)
        {/if}

        <a class="btn btn-light btn-sm float-end"
          href="{Router::link('definition/edit')}/{$def->id}">
          {include "bits/icon.tpl" i=edit}
          editează
        </a>
      </div>

      <div class="card-body">

        {if $mine}
          <form class="mb-3 row row-cols-lg-auto gx-1"" method="post">
            <input type="hidden" name="defId" value="{$def->id}">
            <input type="hidden" name="projectId" value="{$project->id}">

            <div class="col-12">
              <button id="butDown" type="button" class="btn btn-light">
                {include "bits/icon.tpl" i=remove}
              </button>
            </div>

            <div class="col-12">
              <input class="form-control"
                id="errors"
                type="number"
                name="errors"
                value="{$errors}"
                min="0"
                max="999">
            </div>

            <div class="col-12">
              <button id="butUp" type="button" class="btn btn-light">
                {include "bits/icon.tpl" i=add}
              </button>
            </div>

            <div class="col-12">
              <button class="btn btn-primary ms-2" type="submit" name="saveButton">
                {include "bits/icon.tpl" i=save}
                <u>s</u>alvează și preia următoarea
              </button>
            </div>

          </form>
        {/if}

        <p class="currentDef">
          {$def->internalRep}
        </p>

        <p class="currentDef">
          {HtmlConverter::convert($def)}
        </p>

        <div>
          intrări asociate:
          {include "bits/adminEntryList.tpl" entries=$def->getEntries()}
        </div>

        {if count($homonyms)}
          intrări omonime:
          {include "bits/adminEntryList.tpl" entries=$homonyms}
        {/if}

      </div>
    </div>
  {elseif $mine}
    <div class="card mb-3">
      <div class="card-header">Definiția curentă</div>
      <div class="card-body">
        Nu mai există definiții neevaluate. Puteți revizita una dintre
        definițiile de mai jos.
      </div>
    </div>
  {/if}

  <div class="card mb-3">
    <div class="card-header">Raport de acuratețe</div>
    <div class="card-body row">
      <dl class="row col-md-6">
        <dt class="col-md-3">total</dt>
        <dd class="col-md-9">
          {$project->defCount|nf} definiții,
          {$project->totalLength|nf} caractere
        </dd>
        <dt class="col-md-3">eșantion</dt>
        <dd class="col-md-9">
          {$project->getSampleDefinitions()|nf} definiții,
          {$project->getSampleLength()|nf} caractere
        </dd>
        <dt class="col-md-3">evaluate</dt>
        <dd class="col-md-9">
          {$project->getReviewedDefinitions()|nf} definiții,
          {$project->getReviewedLength()|nf} caractere
        </dd>
      </dl>

      <dl class="row col-md-6">
        <dt class="col-md-3">erori</dt>
        <dd class="col-md-9">{$project->getErrorCount()}</dd>
        <dt class="col-md-3">acuratețe</dt>
        <dd class="col-md-9">
          {$project->getAccuracy()|nf:3}%
          ({$project->getErrorsPerKb()|nf:2} erori / 1.000 caractere)
        </dd>
        <dt class="col-md-3">viteză</dt>
        <dd class="col-md-9">
          {if $project->speed}
            {$project->getCharactersPerHour()|nf} caractere / oră
          {else}
            necunoscută
          {/if}
        </dd>
      </dl>
    </div>
  </div>

  <div class="card mb-3">
    <div
      class="card-header collapsed"
      data-bs-toggle="collapse"
      href="#editPanel">
      {include "bits/icon.tpl" i=expand_less class="chevron"}
      {if $mine}
        Editează proiectul
      {else}
        Detalii despre proiect
      {/if}
    </div>

    <div id="editPanel" class="panel-collapse collapse">
      <div class="card-body">

        <form method="post">

          <div class="row mb-2">
            <label class="col-sm-2 form-label">nume</label>
            <div class="col-sm-10">
              <input type="text"
                class="form-control"
                name="name"
                value="{$project->name}"
                {if !$mine}disabled{/if}>
            </div>
          </div>

          <div class="row mb-2">
            <label class="col-sm-2 form-label">vizibilitate</label>
            <div class="col-sm-10">
              {include "bits/dropdown.tpl"
                name="visibility"
                data=AccuracyProject::VIS_NAMES
                selected=$project->visibility
                disabled=!$mine}
            </div>
          </div>

          <div class="row mb-2">
            <label class="col-sm-2 form-label">utilizator</label>
            <label class="col-sm-10 form-label">
              {$project->getUser()->nick}
            </label>
          </div>

          {if $project->sourceId}
            <div class="row mb-2">
              <label class="col-sm-2 form-label">sursă</label>
              <label class="col-sm-10 form-label">
                {$project->getSource()->shortName}
              </label>
            </div>
          {/if}

          {if $project->hasStartDate()}
            <div class="row mb-2">
              <label class="col-sm-2 form-label">dată de început</label>
              <label class="col-sm-10 form-label">
                {$project->startDate}
              </label>
            </div>
          {/if}

          {if $project->hasEndDate()}
            <div class="row mb-2">
              <label class="col-sm-2 form-label">dată de sfârșit</label>
              <label class="col-sm-10 form-label">
                {$project->endDate}
              </label>
            </div>
          {/if}

          {if $project->lexiconPrefix}
            <div class="row mb-2">
              <label class="col-sm-2 form-label">prefix</label>
              <label class="col-sm-10 form-label">
                {$project->lexiconPrefix}
              </label>
            </div>
          {/if}

          {if $mine}
            <div class="row mb-2">
              <div class="col-sm-10 offset-sm-2">
                <button class="btn btn-primary" type="submit" name="editProjectButton">
                  actualizează
                </button>
              </div>
            </div>
          {/if}
        </form>

      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Definiții evaluate</div>
    <div class="card-body">
      <p>Cel mai recent evaluate definiții apar primele. Puteți da clic pentru a le reevalua.</p>

      {foreach $definitionData as $rec name=definitionLoop}
        <a href="?projectId={$project->id}&defId={$rec.id}">
          {$rec.lexicon}
        </a>
        {if $rec.errors}
          ({$rec.errors})
        {/if}
        {if !$smarty.foreach.definitionLoop.last} | {/if}
      {/foreach}
    </div>
  </div>

  <form method="post">
    <input type="hidden" name="projectId" value="{$project->id}">

    <a class="btn btn-light" href="{Router::link('accuracy/projects')}">
      {include "bits/icon.tpl" i=arrow_back}
      înapoi la lista de proiecte
    </a>

    <button class="btn btn-danger" type="submit" id="deleteButton" name="deleteButton">
      {include "bits/icon.tpl" i=delete}
      șterge
    </button>
  </form>
{/block}
