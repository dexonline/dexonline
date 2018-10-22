{extends "layout-admin.tpl"}

{block "title"}
  {if $artist->id}
    Editare autor
  {else}
    Adăugare autor
  {/if}
{/block}

{block "content"}
  <div class="panel panel-default">
    <div class="panel-heading">
      {if $artist->id}
        Editare autor
      {else}
        Adăugare autor
      {/if}
    </div>

    <div class="panel-body">
      <form method="post">
        <input type="hidden" name="id" value="{$artist->id}">

        <div class="form-group">
          <label>Nume</label>
          <input type="text" name="name" value="{$artist->name}" size="50" class="form-control">
        </div>
        <div class="form-group">
          <label>E-mail</label>
          <input type="text" name="email" value="{$artist->email}" size="50" class="form-control">
        </div>
        <div class="form-group">
          <label>Cod</label>
          <input type="text" name="label" value="{$artist->label}" size="30" class="form-control">
        </div>
        <div class="form-group">
          <label>Credite</label>
          <input type="text" name="credits" value="{$artist->credits|escape}" size="80" class="form-control">
        </div>
        <div class="checkbox">
          <label>
            <input type="checkbox" name="sponsor" {if $artist->sponsor}checked{/if}>
            sponsor
            <p class="help-block">
              sponsorii nu sunt asignați automat în lunile viitoare
            </p>
          </label>
        </div>

        <button class="btn btn-success" type="submit" name="saveButton">
          <i class="glyphicon glyphicon-floppy-disk"></i>
          <u>s</u>alvează
        </button>
        <a class="btn btn-link" href="autori-imagini.php">înapoi la lista de autori</a>

      </form>
    </div>
  </div>
{/block}
