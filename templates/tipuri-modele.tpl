{extends "layout-admin.tpl"}

{block "title"}Tipuri de modele{/block}

{block "content"}
  {if $showAddForm}
    <div class="panel panel-default">
      <div class="panel-heading">Adaugă un tip de model nou</div>
      <div class="panel-body">

        <p>
          Notă: prin această interfață nu se pot crea tipuri de model canonice, ci doar redirectări la alte tipuri.
        </p>

        <form method="post" action="tipuri-modele.php">
          <input type="hidden" name="id" value="0">
          <div class="form-group">
            <label>cod</label>
            <input type="text" name="code" value="{$addModelType->code}" size="10" class="form-control">
          </div>

          <div class="form-group">
            <label>cod canonic</label>
            <select class="form-control" name="canonical">
              {foreach $canonicalModelTypes as $mt}
                <option value="{$mt->code}">{$mt->code}</option>
              {/foreach}
            </select>
          </div>

          <div class="form-group">
            <label>descriere</label>
            <input type="text" name="description" value="{$addModelType->description}" class="form-control">
          </div>

          <button class="btn btn-success" type="submit" name="saveButton">
            <i class="glyphicon glyphicon-floppy-disk"></i>
            <u>s</u>alvează
          </button>

          <a class="btn btn-link" href="tipuri-modele">renunță</a>

        </form>
      </div>
    </div>
  {/if}

  {if isset($editModelType)}
    <div class="panel panel-default">
      <div class="panel-heading">Editează tipul de model {$editModelType->code}</div>
      <div class="panel-body">

        <form method="post" action="tipuri-modele.php">
          <input type="hidden" name="id" value="{$editModelType->id}">

          <div class="form-group">
            <label>cod</label>
            <input type="text" value="{$editModelType->code}" disabled class="form-control">
          </div>

          {if $editModelType->code != $editModelType->canonical}
            <div class="form-group">
              <label>cod canonic</label>
              <input type="text" value="{$editModelType->canonical}" disabled class="form-control">
            </div>
            {/if}

          <div class="form-group">
            <label>descriere</label>
            <input type="text" name="description" value="{$editModelType->description}" class="form-control">
          </div>

          <button class="btn btn-success" type="submit" name="saveButton">
            <i class="glyphicon glyphicon-floppy-disk"></i>
            <u>s</u>alvează
          </button>

          <a class="btn btn-link" href="tipuri-modele">renunță</a>
        </form>
      </div>
    </div>
  {/if}

  <table class="table table-condensed table-bordered table-striped col-sm-12">
    <caption class="table-caption">
      Tipuri de modele
    </caption>
    <tr>
      <th>cod</th>
      <th>cod canonic</th>
      <th>descriere</th>
      <th>număr de modele</th>
      <th>număr de lexeme</th>
      <th>acțiuni</th>
    </tr>

    {foreach $modelTypes as $i => $mt}
      <tr>
        <td>{$mt->code}</td>
        <td>{if $mt->code != $mt->canonical}{$mt->canonical}{/if}</td>
        <td>{$mt->description}</td>
        <td>{$modelCounts[$i]}</td>
        <td>{$lexemCounts[$i]}</td>
        <td>
          <a href="?editId={$mt->id}">editează</a>
          {if $canDelete[$i]}
            <a href="?deleteId={$mt->id}">șterge</a>
          {/if}
        </td>
      </tr>
    {/foreach}
  </table>

  {if !$showAddForm}
    <a class="btn btn-default" href="?add=1">
      <i class="glyphicon glyphicon-plus"></i>
      adaugă un tip de model
    </a>
  {/if}
{/block}
