{extends "layout-admin.tpl"}

{block "title"}Etichetare imagine{/block}

{block "content"}
  <h3>Etichetare imagine {$visual->path}</h3>

  <p>
    <a class="btn btn-link" href="{Router::link('visual/list')}">
      {include "bits/icon.tpl" i=arrow_back}
      înapoi la pagina de imagini
    </a>
  </p>

  {include "bits/galleryCanvas.tpl"}

  <div class="row ms-1 mb-3">
    <div class="col-md-6 px-0 imageHolder">
      <img id="jcrop" src="{Config::STATIC_URL}/img/visual/{$visual->path}">
    </div>

    <div class="col-md-6">

      <div class="card mb-3">
        <div class="card-header">
          Informații globale
        </div>
        <div class="card-body">
          <form method="post">
            <input id="visualId" type="hidden" name="id" value="{$visual->id}">

            <div class="row mb-1">
              <label class="col-sm-3 col-form-label">
                intrare
              </label>
              <div class="col-sm-9">
                <select id="entryId" class="form-select" name="entryId">
                  <option value="{$visual->entryId}" selected></option>
                </select>
              </div>
            </div>

            <div class="row mb-2">
              <div class="col-sm-9 offset-sm-3">
                <div class="form-check">
                  <label class="form-check-label">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      name="revised"
                      value="1"
                      {if $visual->revised}checked{/if}>
                    etichetarea este completă
                  </label>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-9 offset-sm-3">
                <button type="submit" class="btn btn-primary" name="saveButton">
                  {include "bits/icon.tpl" i=save}
                  <u>s</u>alvează
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          Adaugă o etichetă nouă
        </div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="id" value="{$visual->id}">

            <div class="row mb-3">
              <label class="col-sm-3 col-form-label">
                intrare
              </label>
              <div class="col-sm-9">
                <select id="tagEntryId" class="form-select" name="tagEntryId">
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-3 col-form-label">
                text afișat
              </label>
              <div class="col-sm-9">
                <input id="tagLabel" class="form-control" type="text" name="tagLabel">
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-xl-3 col-form-label">
                coordonatele etichetei
              </label>
              <div class="col-xl-9 d-flex">
                <div class="flex-shrink-1">
                  <input
                    id="textXCoord"
                    class="form-control"
                    name="textXCoord"
                    type="text"
                    placeholder="X"
                    size="4">
                </div>
                <div class="flex-shrink-1 mx-1">
                  <input
                    id="textYCoord"
                    class="form-control"
                    name="textYCoord"
                    type="text"
                    placeholder="Y"
                    size="4">
                </div>
                <button id="setTextCoords" class="btn btn-primary" type="button">
                  {include "bits/icon.tpl" i=content_copy}
                  copiază
                </button>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-xl-3 col-form-label">
                coordonatele săgeții
              </label>
              <div class="col-xl-9 d-flex">
                <div class="flex-shrink-1">
                  <input
                    id="imgXCoord"
                    class="form-control"
                    name="imgXCoord"
                    type="text"
                    placeholder="X"
                    size="4">
                </div>
                <div class="flex-shrink-1 mx-1">
                  <input
                    id="imgYCoord"
                    class="form-control"
                    name="imgYCoord"
                    type="text"
                    placeholder="Y"
                    size="4">
                </div>
                <button id="setImgCoords" class="btn btn-primary" type="button">
                  {include "bits/icon.tpl" i=content_copy}
                  copiază
                </button>
              </div>
              <div class="form-text col-xl-9 offset-xl-3">
                Pentru a copia coordonate, click pe imagine, apoi click pe butonul [copiază]
              </div>
            </div>

            <div class="row">
              <div class="col-sm-9 offset-sm-3">
                <button id="addTagButton" type="submit" class="btn btn-primary" name="addTagButton">
                  {include "bits/icon.tpl" i=save}
                  salvează eticheta
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          Previzualizare
        </div>
        <div class="card-body">
          <button id="previewTags" type="button" class="btn btn-primary">
            {include "bits/icon.tpl" i=visibility}
            <u>p</u>revizualizează etichetele
          </button>
        </div>
      </div>

    </div>
  </div>

  <h3>Etichete existente</h3>

  <table id="tagsGrid"></table>
  <div id="tagsPaging"></div>

{/block}
