{extends "layout-admin.tpl"}

{block "title"}Adaugă definiții OCR din dicționar{/block}


{block "content"}
  <h3>Adaugă definiții OCR din dicționar</h3>

  {if $message}
    {notice type=$msgType}
      {$message}
    {/notice}
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
          <option value="0">linie nouă (o definiție pe linie)</option>
          <option value="1" selected="selected">linie dublă (definiții separate de o linie goală)</option>
          <option value="2">linie triplă (definiții separate de două linii goală)</option>
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

{/block}
