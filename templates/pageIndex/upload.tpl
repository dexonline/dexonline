{extends "layout-admin.tpl"}

{block "title"}Adaugă indecși de pagină pentru dicționar{/block}


{block "content"}
  <h3>Adaugă indecși de pagină pentru dicționar</h3>

  <form class="form-horizontal" method="post" enctype="multipart/form-data">

    {* No page index loaded yet -- show file selector and legend *}
    {if empty($indexes)}
      <div class="panel panel-default">

        <div class="panel-heading">
          Selectare fișier
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-sm-1 control-label">fișier</label>
            <div class="col-sm-6">
              <input class="form-control" type="file" name="file">
            </div>
            <label class="col-sm-2 control-label">delimitator</label>
            <div class="col-sm-2">
              <input class="form-control"
                     type="text"
                     name="delimiter"
                     placeholder="implicit |">
            </div>
            <span class="col-sm-offset-1 col-sm-8 text-danger">
              Important! Asigurați-vă că fișierul este codificat ASCII sau UTF-8.
            </span>
          </div>

          <p class="text-muted">

            Fișierul sursă trebuie să aibă pe primul rând capul de tabel
            <b>volume|page|word|number</b>,
            iar pe celelate rânduri 4 (patru) câmpuri delimitate, conform explicațiilor: </br>

          <ol>
            <li> volum – <i>întreg (pozitiv), valoarea implicită 1</i></li>
            <li> pagină – <i>întreg (pozitiv)</i></li>
            <li> intrare – <i>șir de caractere</i></li>
            <li> număr – <i>întreg (pozitiv), valoarea implicită 0</i></li>
          </ol>
          </p>
        </div>
      </div>

      <div class="form-group">
        <div class="col-sm-10">
          <button class="btn btn-primary" type="submit" name="submit">
            încarcă
          </button>
        </div>
      </div>
    {/if}

    {* Abbreviations loaded -- show source dropdown and preview *}
    {if !empty($indexes)}
      <div class="panel panel-default">

        <div class="panel-heading">
          Alegere sursă
        </div>

        <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-1 control-label">sursa</label>
            <div class="col-sm-11">
              {include "bits/sourceDropDown.tpl" skipAnySource=true}
            </div>
          </div>
        </div>

      </div>

      <div class="panel-admin">
        <div class="panel panel-default">
          <div class="panel-heading" id="panel-heading">
            <i class="glyphicon glyphicon-user"></i>
            {$modUser}
          </div>

          <table id="indexes" class="table table-striped ">
            <thead>
            <tr>
              <th>Nr.</th>
              <th>Vol.</th>
              <th>Pag.</th>
              <th>Intrare</th>
              <th>Nr.cuv.</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$indexes key=k item=pi}
              <tr>
                <td><span class="label label-default">{$k+1}</span></td>
                <td><span class="volume">{$pi->volume}</span></td>
                <td><span class="page">{$pi->page}</span></td>
                <td><span class="word">{$pi->word}</span></td>
                <td><span class="number">{$pi->number}</span></td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        </div>
      </div>

      <div class="form-group">
        <div class="col-sm-10">

          <button class="btn btn-success" name="saveButton">
            <i class="glyphicon glyphicon-floppy-disk"></i>
            <u>s</u>alvează
          </button>
          <button class="btn btn-primary" name="cancelButton">
            <i class="glyphicon glyphicon-remove"></i>
            abandonează
          </button>

        </div>
      </div>
    {/if}
  </form>

{/block}
