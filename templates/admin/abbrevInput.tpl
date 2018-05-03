{extends "layout-admin.tpl"}

{block "title"}Adaugă abrevieri pentru dicționar{/block}


{block "content"}
  <h3>Adaugă abrevieri pentru dicționar</h3>

  {if $message}
    <div class="alert alert-{$msgClass} alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      {$message}
    </div>
  {/if}
  <div class="panel panel-default">
    <div class="panel-heading">
      {if empty($abbrevs)}
        Selectare fișier
      {else}
        Alegere sursă
      {/if}
    </div>
    <div class="panel-body">
      <form class="form-horizontal" method="post" enctype="multipart/form-data">
        {if !empty($abbrevs)}
          <div class="form-group">
            <label class="col-sm-1 control-label">sursa</label>
            <div class="col-sm-11">
              {include "bits/sourceDropDown.tpl" skipAnySource=true}
            </div>
          </div>
        {/if}

        {if empty($abbrevs)}
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
            Fișierul sursă trebuie să aibă pe primul rând capul de tabel <b>enforced|ambiguous|caseSensitive|short|internalRep</b>, iar pe celelate rânduri 5(cinci) câmpuri delimitate, conform explicațiilor: </br>
          <ol>
            <li> abreviere impusă - nu ia în considerare forma editată și impune forma din câmpul short - <i>valoare booleană 0/1</i></li>
            <li> abreviere ambiguă - <i>valoare booleană 0/1</i></li>
            <li> diferențiere între majuscule și minuscule - <i>valoare booleană 0/1</i></li>
            <li> abrevierea - <i>permite și alte semne de punctuație, nu doar „.” + formatare internă $,@,%,_{},^{}</i></li>
            <li> detalierea abrevierii - <i>permite formatare internă html $,@,%,_{},^{}</i></li>
          </ol>
          </p>
        {/if}
    </div>
  </div>
  {if empty($abbrevs)}
    <div class="form-group">
      <div class="col-sm-10">
        <button class="btn btn-primary" type="submit" name="submit">
          încarcă
        </button>
      </div>
    </div>
  {/if}


  {if !empty($abbrevs)}
    <div class="panel-admin">
      <div class="panel panel-default">
        <div class="panel-heading" id="panel-heading">
          <i class="glyphicon glyphicon-user"></i>
          {$modUser}
        </div>

        <table id="abbrevs" class="table table-striped ">
          <thead>
            <tr>
              <th>Nr.</th>
              <th>Imp.</th>
              <th>Amb.</th>
              <th>CS</th>
              <th>Abreviere</th>
              <th>Detalierea abrevierii</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$abbrevs key=k item=a}
              <tr>
                <td><span class="label label-default">{$k+1}</span></td>
                <td><span class="enforced">{$a->enforced}</span></td>
                <td><span class="ambiguous">{$a->ambiguous}</span></td>
                <td><span class="caseSensitive">{$a->caseSensitive}</span></td>
                <td><span class="short">{$a->short}</span></td>
                <td><span class="html">{HtmlConverter::convert($a)}</span></td>
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
