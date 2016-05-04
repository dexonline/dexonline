{extends file="layout.tpl"}

{block name=title}{$project->name} | verificarea acurateței{/block}

{block name=content}
  <h2>Proiect de verificarea a acurateței - {$project->name}</h2>

  <h3>Definiția curentă</h3>

  <div class="defActions">
    <form method="post">
      <input type="hidden" name="defId" value="{$def->id}">
      <input type="hidden" name="projectId" value="{$project->id}">

      <button id="butDown" type="button" class="sign">&ndash;</button>
      <input id="errors" type="number" name="errors" value="{$errors}" min="0" max="999">
      <button id="butUp" type="button" class="sign">+</button>

      <button class="spacer" type="submit" name="submitButton" value="1">
        Salvează și preia următoarea
      </button>

      <div class="links">
        <a href="admin/definitionEdit.php?id={$def->id}">editează definiția</a>
      </div>
    </form>
  </div>

  <div class="currentDef">
    <div class="defComment">
      {$def->internalRep}
    </div>

    <div class="defComment">
      {$def->htmlRep}
    </div>
  </div>

  <h3>Detalii despre proiect</h3>

  <table class="minimalistTable">
    <tr>
      <td>utilizator evaluat</td>
      <td>{$project->getUser()->nick}</td>
    </tr>
    {if $project->sourceId}
      <tr>
        <td>sursă</td>
        <td>{$project->getSource()->shortName}</td>
      </tr>
    {/if}
    {if $project->hasStartDate()}
      <tr>
        <td>dată de început</td>
        <td>{$project->startDate}</td>
      </tr>
    {/if}
    {if $project->hasEndDate()}
      <tr>
        <td>dată de sfârșit</td>
        <td>{$project->endDate}</td>
      </tr>
    {/if}
    <tr>
      <td>metodă</td>
      <td>{$project->getMethodName()}</td>
    </tr>
  </table>

  <h3>Raport de acuratețe</h3>

  <table class="minimalistTable">
    <tr>
      <td>Total definiții</td>
      <td>{$accuracyData.defCount}</td>
    </tr>
    <tr>
      <td>Definiții evaluate</td>
      <td>{$accuracyData.evalCount}</td>
    </tr>
    <tr>
      <td>Caractere evaluate</td>
      <td>{$accuracyData.evalLength}</td>
    </tr>
    <tr>
      <td>Erori</td>
      <td>{$accuracyData.errors}</td>
    </tr>
    <tr>
      <td>Acuratețe</td>
      <td>{$accuracyData.accuracy|string_format:"%.3f"}</td>
    </tr>
  </table>

  <h3>Definiții evaluate (click pentru a le reevalua)</h3>

  {foreach $definitionData as $rec}
    <a href="?projectId={$project->id}&defId={$rec.id}">{$rec.lexicon}</a>
  {/foreach}

  <div>
    <a href="acuratete">înapoi la lista de proiecte</a>
  </div>

{/block}
