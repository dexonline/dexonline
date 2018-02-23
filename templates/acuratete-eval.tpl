{extends "layout-admin.tpl"}

{block "title"}{$project->name} | verificarea acurateței{/block}

{block "content"}
  <h3>Proiect de verificare a acurateței - {$project->name}</h3>

  {if $def}
    <div class="panel panel-default">
      <div class="panel-heading">
        Definiția curentă
        {if !$mine}
          ({$errors} erori)
        {/if}

        <a class="btn btn-default btn-xs pull-right"
           href="admin/definitionEdit.php?definitionId={$def->id}">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      </div>

      <div class="panel-body">

        {if $mine}
          <form class="form-inline" method="post">
            <input type="hidden" name="defId" value="{$def->id}">
            <input type="hidden" name="projectId" value="{$project->id}">

            <button id="butDown" type="button" class="btn btn-default">&ndash;</button>
            <input class="form-control"
                   id="errors"
                   type="number"
                   name="errors"
                   value="{$errors}"
                   min="0"
                   max="999">
            <button id="butUp" type="button" class="btn btn-default">+</button>

            <button class="btn btn-success" type="submit" name="saveButton">
              <i class="glyphicon glyphicon-floppy-disk"></i>
              <u>s</u>alvează și preia următoarea
            </button>

          </form>
        {/if}

        <div class="voffset2">
          <p class="currentDef">
            {$def->internalRep}
          </p>

          <p class="currentDef">
            {$def->htmlRep}
          </p>
        </div>

        <div>
          intrări asociate:
          {foreach $def->getEntries() as $e name=entryLoop}
            {include "bits/entry.tpl" entry=$e editLink=true}
            {if !$smarty.foreach.entryLoop.last} | {/if}
          {/foreach}
        </div>

        {if count($homonyms)}
          intrări omonime:
          {foreach $homonyms as $h name=homonymLoop}
            {include "bits/entry.tpl" entry=$h editLink=true}
            {if !$smarty.foreach.homonymLoop.last} | {/if}
          {/foreach}
        {/if}

      </div>
    </div>
  {elseif $mine}
    <div class="panel panel-default">
      <div class="panel-heading">Definiția curentă</div>
      <div class="panel-body">
        Nu mai există definiții neevaluate. Puteți revizita una dintre
        definițiile de mai jos.
      </div>
    </div>
  {/if}

  <div class="panel panel-default">
    <div class="panel-heading">Raport de acuratețe</div>
    <div class="panel-body row">
      <dl class="dl-horizontal col-md-6">
        <dt>total</dt>
        <dd>
          {$project->defCount|number_format:0:',':'.'} definiții,
          {$project->totalLength|number_format:0:',':'.'} caractere
        </dd>
        <dt>eșantion</dt>
        <dd>
          {$project->getProjectDefCount()|number_format:0:',':'.'} definiții,
          {$project->getProjectLength()|number_format:0:',':'.'} caractere
        </dd>
        <dt>evaluate</dt>
        <dd>
          {$project->getEvalCount()|number_format:0:',':'.'} definiții,
          {$project->getEvalLength()|number_format:0:',':'.'} caractere
        </dd>
      </dl>

      <dl class="dl-horizontal col-md-6">
        <dt>erori</dt>
        <dd>{$project->getErrorCount()}</dd>
        <dt>acuratețe</dt>
        <dd>
          {$project->getAccuracy()|string_format:"%.3f"}%
          ({$project->getErrorsPerKb()|string_format:"%.2f"} erori / 1.000 caractere)
        </dd>
        <dt>viteză</dt>
        <dd>
          {if $project->speed}
            {$project->getCharactersPerHour()|number_format:0:',':'.'} caractere / oră
          {else}
            necunoscută
          {/if}
        </dd>
      </dl>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <a class="collapsed"
         data-toggle="collapse"
         href="#editPanel">
        <i class="pull-right glyphicon glyphicon-chevron-down"></i>
        {if $mine}
          Editează proiectul
        {else}
          Detalii despre proiect
        {/if}
      </a>
    </div>

    <div id="editPanel" class="panel-collapse collapse">
      <div class="panel-body">

        <form class="form-horizontal" method="post">

          <div class="form-group">
            <label class="col-sm-2 control-label">nume</label>
            <div class="col-sm-10">
              <input type="text"
                     class="form-control"
                     name="name"
                     value="{$project->name}"
                     {if !$mine}disabled{/if}>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">vizibilitate</label>
            <div class="col-sm-10">
              {include "bits/dropdown.tpl"
              name="visibility"
              data=AccuracyProject::$VIS_NAMES
              selected=$project->visibility
              disabled=!$mine}
            </div>
          </div>

          <div class="form-group">

            <div>
              <label class="col-sm-2 control-label">utilizator</label>
              <div class="col-sm-10">
                <p class="form-control-static">
                  {$project->getUser()->nick}
                </p>
              </div>
            </div>

            {if $project->sourceId}
              <div>
                <label class="col-sm-2 control-label">sursă</label>
                <div class="col-sm-10">
                  <p class="form-control-static">
                    {$project->getSource()->shortName}
                  </p>
                </div>
              </div>
            {/if}

            {if $project->hasStartDate()}
              <div>
                <label class="col-sm-2 control-label">dată de început</label>
                <div class="col-sm-10">
                  <p class="form-control-static">
                    {$project->startDate}
                  </p>
                </div>
              </div>
            {/if}

            {if $project->hasEndDate()}
              <div>
                <label class="col-sm-2 control-label">dată de sfârșit</label>
                <div class="col-sm-10">
                  <p class="form-control-static">
                    {$project->endDate}
                  </p>
                </div>
              </div>
            {/if}

          </div>

          {if $mine}
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-8">
                <button class="btn btn-success" type="submit" name="editProjectButton">
                  actualizează
                </button>
              </div>
            </div>
          {/if}
        </form>

      </div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Definiții evaluate</div>
    <div class="panel-body">
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

    <a class="btn btn-default" href="acuratete">
      <i class="glyphicon glyphicon-arrow-left"></i>
      înapoi la lista de proiecte
    </a>

    <button class="btn btn-danger" type="submit" id="deleteButton" name="deleteButton">
      <i class="glyphicon glyphicon-trash"></i>
      șterge
    </button>
  </form>
{/block}
