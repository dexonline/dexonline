{extends "layout-admin.tpl"}

{block "title"}Etichetare imagine{/block}

{block "content"}
  <h3>Etichetare imagine {$visual->path}</h3>

  <p>
    <a class="btn btn-default" href="visual.php">
      <i class="glyphicon glyphicon-arrow-left"></i>
      înapoi la pagina de imagini
    </a>
  </p>

  {include "bits/galleryCanvas.tpl"}

  <div class="row">
    <div class="col-md-6 imageHolder">
      <img id="jcrop" src="{Config::STATIC_URL}/img/visual/{$visual->path}">
    </div>

    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          Informații globale
        </div>
        <div class="panel-body">
          <form class="form-horizontal" method="post">
            <input id="visualId" type="hidden" name="id" value="{$visual->id}">

            <div class="form-group">
              <label class="col-sm-3 control-label">
                intrare
              </label>
              <div class="col-sm-9">
                <select id="entryId" class="form-control" name="entryId">
                  <option value="{$visual->entryId}" selected></option>
                </select>
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="revised" value="1" {if $visual->revised}checked{/if}>
                    etichetarea este completă
                  </label>
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-success" name="saveButton">
                  <i class="glyphicon glyphicon-floppy-disk"></i>
                  <u>s</u>alvează
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          Adaugă o etichetă nouă
        </div>
        <div class="panel-body">
          <form class="form-horizontal" method="post">
            <input type="hidden" name="id" value="{$visual->id}">

            <div class="form-group">
              <label class="col-sm-3 control-label">
                intrare
              </label>
              <div class="col-sm-9">
                <select id="tagEntryId" class="form-control" name="tagEntryId">
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">
                text afișat
              </label>
              <div class="col-sm-9">
                <input id="tagLabel" class="form-control" type="text" name="tagLabel">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">
                coordonatele etichetei
              </label>
              <div class="col-sm-9 form-inline">
                <input id="textXCoord"
                       class="form-control"
                       name="textXCoord"
                       type="text"
                       placeholder="X"
                       size="4">
                <input id="textYCoord"
                       class="form-control"
                       name="textYCoord"
                       type="text"
                       placeholder="Y"
                       size="4">
                <button id="setTextCoords" class="btn btn-primary" type="button">
                  copiază coordonatele
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">
                coordonatele săgeții
              </label>
              <div class="col-sm-9 form-inline">
                <div>
                  <input id="imgXCoord"
                         class="form-control"
                         name="imgXCoord"
                         type="text"
                         placeholder="X"
                         size="4">
                  <input id="imgYCoord"
                         class="form-control"
                         name="imgYCoord"
                         type="text"
                         placeholder="Y"
                         size="4">
                  <button id="setImgCoords" class="btn btn-primary" type="button">
                    copiază coordonatele
                  </button>
                </div>
                <div class="text-muted">
                  Pentru a copia coordonate, click pe imagine, apoi click pe butonul [copiază]
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <button id="addTagButton" type="submit" class="btn btn-success" name="addTagButton">
                  <i class="glyphicon glyphicon-floppy-disk"></i>
                  salvează eticheta
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          Previzualizare
        </div>
        <div class="panel-body">
          <form class="form-horizontal" method="post">

            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <button id="previewTags" type="button" class="btn btn-success">
                  <i class="glyphicon glyphicon-eye-open"></i>
                  <u>p</u>revizualizează etichetele
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>

  <h3>Etichete existente</h3>

  <table id="tagsGrid"></table>
  <div id="tagsPaging"></div>

{/block}
