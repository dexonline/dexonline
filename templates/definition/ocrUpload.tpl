{extends "layout-admin.tpl"}

{block "title"}Adaugă definiții OCR din dicționar{/block}


{block "content"}
  <h3>Adaugă definiții OCR din dicționar</h3>

  {if $message}
    <div class="alert alert-{$msgClass} alert-dismissible fade show">
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
      {$message}
    </div>
  {/if}

  <form method="post" enctype="multipart/form-data">
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">sursa</label>
      <div class="col-sm-10">
        {include "bits/sourceDropDown.tpl" sources=$allModeratorSources skipAnySource=true}
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">moderator</label>
      <div class="col-sm-10">
        {include "bits/moderatorDropDown.tpl" name="editor" moderators=$allOCRModerators}
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">fișier</label>
      <div class="col-sm-10">
        <input class="form-control" type="file" name="file">
      </div>
      <span class="col-sm-10 offset-sm-2">
        <span class="form-text">
          <span class="text-danger">
            Important:
          </span>
          Asigurați-vă că fișierul este codificat ASCII sau UTF-8. Alte
          codificări vor genera erori la import.
        </span>
      </span>
    </div>

    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">terminator</label>
      <div class="col-sm-10">
        <select class="form-select" name="term">
          <option value="0">linie nouă</option>
          <option value="1" selected="selected">linie dublă</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-sm-10 offset-sm-2">
        <button class="btn btn-primary" type="submit" name="submit">
          încarcă
        </button>
      </div>
    </div>
  </form>

  {* Am unificat blocul stats cu blocul content *}
  <h4 class="mt-4">Alocare definiții OCR</h4>

  <table class="table table-sm table-hover">

    <thead>
      <tr>
        <th>Moderator</th>
        <th>Definiții finalizate</th>
        <th>Definiții alocate</th>
        <th>Caractere alocate</th>
      </tr>
    </thead>

    <tbody>
      {foreach $statsEditors as $i}
        <tr>
          <td>{$i.0}</td>
          <td>{$i.1}</td>
          <td>{$i.2}</td>
          <td>{$i.4}</td>
        </tr>
      {/foreach}
    </tbody>

  </table>

  <h4 class="mt-4">Dicționare prelucrate</h4>

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
