{extends "layout-admin.tpl"}

{block "title"}Adaugă indexul de pagini pentru dicționar{/block}


{block "content"}
  <h3>Adaugă indexul de pagini pentru dicționar</h3>

  <form class="form-horizontal" method="post" enctype="multipart/form-data">

    {* No pageindex loaded yet -- show file selector and legend *}
    {if empty($pages)}
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
            iar pe celelate rânduri 4(patru) câmpuri delimitate, conform explicațiilor: </br>

            <ol>
              <li> volumul dicționarului - <i>valoare numerică</i></li>
              <li> pagina din volumul respectiv - <i>valoare numerică</i></li>
              <li> primul cuvânt glosat - <i>cu diacritice când e cazul</i></li>
              <li> indice sau exponent pentru omonime/omografe - <i>valoare numerică</i></li>
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

    {* PageIndex loaded -- show source dropdown and preview *}
    {if !empty($pages)}
      <div class="panel panel-default">

        <div class="panel-heading">
          Alegere sursă
        </div>

        <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-1 control-label">sursa</label>
            <div class="col-sm-11">
              {include "bits/sourceDropdown.tpl" id=$sources.vars.id}
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

          <table id="pageindex" class="table table-striped ">
            <thead>
              <tr>
                <th>Nr.</th>
                <th>Volum</th>
                <th>Pagină</th>
                <th style="width:50%">Cuvânt</th>
                <th>Indice/Exponent</th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$pages key=k item=p}
                <tr>
                  <td><span class="label label-default">{$k+1}</span></td>
                  <td><span class="volume">{$p->volume}</span></td>
                  <td><span class="page">{$p->page}</span></td>
                  <td><span class="word">{$p->word|escape}</span></td>
                  <td><span class="number">{$p->number}</span></td>
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
