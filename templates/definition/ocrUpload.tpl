{extends "layout-admin.tpl"}

{block "title"}Adaugă definiții OCR din dicționar{/block}


{block "content"}
  <h3>Adaugă definiții OCR din dicționar</h3>

  {if $message}
    <div class="alert alert-{$msgClass} alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      {$message}
    </div>
  {/if}

  <form class="form-horizontal" method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label class="col-sm-2 control-label">sursa</label>
      <div class="col-sm-10">
        {include "bits/sourceDropdown.tpl" id=$sources.vars.id}
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">moderator</label>
      <div class="col-sm-10">
        {include "bits/userDropdown.tpl"}
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">fișier</label>
      <div class="col-sm-10">
        <input class="form-control" type="file" name="file">
      </div>
      <span class="col-sm-offset-2 col-sm-10 text-danger">
            Important! Asigurați-vă că fișierul este codificat ASCII sau UTF-8. Alte codificări vor genera erori la import!
      </span>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">terminator</label>
      <div class="col-sm-10">
        <select class="form-control" name="term">
          <option value="0">linie nouă</option>
          <option value="1">linie dublă</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button class="btn btn-primary" type="submit" name="submit">
          încarcă
        </button>
      </div>
    </div>
  </form>

  {* Am unificat blocul stats cu blocul content *}
  <h4>Alocare definiții OCR</h4>

  <table class="table table-condensed table-striped">

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

  <h4>Dicționare prelucrate</h4>

  <table class="table table-condensed table-striped">

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
