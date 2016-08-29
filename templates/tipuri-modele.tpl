{extends file="layout.tpl"}

{block name=title}Tipuri de modele{/block}

{block name=banner}{/block}
{block name=search}{/block}

{block name=content}
  {if $showAddForm}
    <div class="panel panel-default">
      <div class="panel-heading">Adaugă un tip de model nou</div>
      <div class="panel-body">

        <p>
          Notă: prin această interfață nu se pot crea tipuri de model canonice, ci doar redirectări la alte tipuri.
        </p>

        <form method="post" action="tipuri-modele.php">
          <input type="hidden" name="id" value="0" />
          <div class="form-group">
            <label>Cod</label>
            <input type="text" name="code" value="{$addModelType->code}" size="10" class="form-control" />
          </div>

          <div class="form-group">
            <label>Cod canonic</label>
            <select class="form-control" name="canonical">
              {foreach from=$canonicalModelTypes item=mt}
                <option value="{$mt->code}">{$mt->code}</option>
              {/foreach}
            </select>
          </div>

          <div class="form-group">
            <label>Descriere</label>
            <input type="text" name="description" value="{$addModelType->description}" class="form-control" />
          </div>

          <input class="btn btn-primary" type="submit" name="submitAddButton" value="acceptă" />
          <a href="tipuri-modele">renunță</a>
        </form>
      </div>
    </div>
  {/if}

  {if isset($editModelType)}
    <div class="panel panel-default">
      <div class="panel-heading">Editează tipul de model {$editModelType->code}</div>
      <div class="panel-body">

        <form method="post" action="tipuri-modele.php">
          <input type="hidden" name="id" value="{$editModelType->id}" />

          <div class="form-group">
            <label>Cod</label>
            <input type="text" value="{$editModelType->code}" disabled="disabled" class="form-control" />
          </div>

          {if $editModelType->code != $editModelType->canonical}
            <div class="form-group">
              <label>Cod canonic</label>
              <input type="text" value="{$editModelType->canonical}" disabled="disabled" class="form-control" />
            </div>
            {/if}

          <div class="form-group">
            <label>descriere</label>
            <input type="text" name="description" value="{$editModelType->description}" class="form-control" />
          </div>

          <input type="submit" name="submitEditButton" class="btn btn-primary" value="acceptă" />
          <a class="btn btn-link" href="tipuri-modele">renunță</a>
        </form>
      </div>
    </div>
  {/if}

  <table class="table-condensed table-bordered table-striped col-sm-12">
    <caption class="table-caption">
      Tipuri de modele
      {if !$showAddForm}
        <a class="btn btn-xs btn-success pull-right" href="?add=1">adaugă un tip de model</a>
      {/if}
    </caption>
    <tr>
      <th>cod</th>
      <th>cod canonic</th>
      <th>descriere</th>
      <th>număr de modele</th>
      <th>număr de lexeme</th>
      <th>acțiuni</th>
    </tr>

    {foreach from=$modelTypes item=mt key=i}
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

{/block}
