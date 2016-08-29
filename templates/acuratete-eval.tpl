{extends file="layout.tpl"}

{block name=title}{$project->name} | verificarea acurateței{/block}

{block name=banner}{/block}
{block name=search}{/block}

{block name=content}
  <h2>Proiect de verificare a acurateței - {$project->name}</h2>

  <div class="panel panel-default">
    <div class="panel-heading">
      Definiția curentă
      <a class="btn btn-xs btn-default pull-right" href="acuratete">înapoi la lista de proiecte</a>
    </div>
    <div class="panel-body">

      <form class="form-inline" method="post">
        {if $def}
          <input type="hidden" name="defId" value="{$def->id}">
        {/if}
        <input type="hidden" name="projectId" value="{$project->id}">

        <button id="butDown" type="button" class="btn btn-default">&ndash;</button>
        <input class="form-control" id="errors" type="number" name="errors" value="{$errors}" min="0" max="999">
        <button id="butUp" type="button" class="btn btn-default">+</button>

        <button class="btn btn-success" type="submit" name="submitButton" value="1">
          Salvează și preia următoarea
        </button>

        {if $def}
          <a class="btn btn-warning" href="admin/definitionEdit.php?definitionId={$def->id}">editează definiția</a>
        {/if}
      </form>

      <br />

      <div class="well">
        {if $def}
          <div class="defComment">
            {$def->internalRep}
          </div>

          <div class="defComment">
            {$def->htmlRep}
          </div>
        {else}
          Nu mai există definiții de evaluat. Dumneavoastră sau alt evaluator le-ați evaluat pe toate.
        {/if}
      </div>

    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Raport de acuratețe</div>
    <div class="panel-body">
      <dl class="dl-horizontal">
        <dt>total definiții</dt>
        <dd>{$accuracyData.defCount}</dd>
        <dt>definiții evaluate</dt>
        <dd>{$accuracyData.evalCount}</dd>
        <dt>caractere evaluate</dt>
        <dd>{$accuracyData.evalLength}</dd>
        <dt>erori</dt>
        <dd>{$accuracyData.errors}</dd>
        <dt>acuratețe</dt>
        <dd>
          {$accuracyData.accuracy|string_format:"%.3f"}%
          ({$accuracyData.errorRate|string_format:"%.2f"} erori / 1.000 caractere)
        </dd>
      </dl>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">Detalii despre proiect</div>
    <div class="panel-body">

      <dl class="dl-horizontal">
        <dt>utilizator</dt>
        <dd>{$project->getUser()->nick}</dd>
        {if $project->sourceId}
          <dt>sursă</dt>
          <dd>{$project->getSource()->shortName}</dd>
        {/if}
        {if $project->hasStartDate()}
          <dt>dată de început</dt>
          <dd>{$project->startDate}</dd>
        {/if}
        {if $project->hasEndDate()}
          <dt>dată de sfârșit</dt>
          <dd>{$project->endDate}</dd>
        {/if}
        <dt>metodă</dt>
        <dd>{$project->getMethodName()}</dd>
      </dl>

    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Definiții evaluate</div>
    <div class="panel-body">
      <p>Cel mai recent evaluate definiții apar primele. Puteți da clic pentru a le reevalua.</p>

      {foreach $definitionData as $rec}
        <a href="?projectId={$project->id}&defId={$rec.id}">{$rec.lexicon}</a>
      {/foreach}
    </div>
  </div>

{/block}
