{extends "layout-admin.tpl"}

{block "title"}Informații despre definițiile OCR{/block}

{block "content"}
  <h3>Informații despre definițiile OCR</h3>

  {if $message}
    {notice type=$msgType}
    {$message}
    {/notice}
  {/if}

  <div class="card mb-3">
    <div class="card-header">
      Împrospătează statisticile (ultima actualizare la {$statsTS})
    </div>

    <div class="card-body">
      <form>
        <button type="submit" class="btn btn-primary" name="refreshStatsButton">
          regenerează statisticile
        </button>
      </form>
    </div>
  </div>

  {* Am unificat blocul stats cu blocul content *}
  <h4 class="mt-4">Alocarea definițiilor OCR</h4>

  <table class="table table-sm table-hover">

    <thead>
    <tr>
      <th>Moderator</th>
      <th>Definiții finalizate</th>
      <th>Dicționar în lucru</th>
      <th>Definiții alocate</th>
      <th>Caractere alocate</th>
    </tr>
    </thead>

    <tbody>
    {foreach $statsEditors as $i}
      <tr>
        <td>{$i.0}</td>
        <td>{$i.1}</td>
        <td>{$i.5}</td>
        <td>{$i.2}</td>
        <td>{$i.4}</td>
      </tr>
    {/foreach}
    </tbody>

  </table>

  {* if empty($statsStudents) *}
  <h4 class="mt-4">Studenți</h4>

  <table class="table table-sm table-hover">

    <thead>
    <tr>
      <th>Student</th>
      <th>An</th>
      <th>Definiții finalizate</th>
      <th>Dicționar în lucru</th>
      <th>Definiții alocate</th>
      <th>Caractere alocate</th>
    </tr>
    </thead>

    <tbody>
    {foreach $statsStudents as $i}
      <tr>
        <td>{$i.0}</td>
        <td>{$i.6}</td>
        <td>{$i.1}</td>
        <td>{$i.5}</td>
        <td>{$i.2}</td>
        <td>{$i.4}</td>
      </tr>
    {/foreach}
    </tbody>

  </table>
  {* /if *}

  <h4 class="mt-4">Dicționare prelucrate OCR</h4>

  <table class="table table-sm table-hover">

    <thead>
    <tr>
      <th>Preparator</th>
      <th>Dicționar</th>
      <th>Definiții preparate</th>
      <th>Definiții în lucru</th>
      <th>Nr. caractere preparate</th>
      <th>Nr. caractere în lucru</th>
    </tr>
    </thead>

    <tbody>
    {foreach $statsPrep as $i}
      <tr>
        <td>{$i.0}</td>
        <td>{$i.1}</td>
        <td>{$i.2}</td>
        <td>{$i.3}</td>
        <td>{$i.4}</td>
        <td>{$i.5}</td>
      </tr>
    {/foreach}
    </tbody>

  </table>

{/block}
