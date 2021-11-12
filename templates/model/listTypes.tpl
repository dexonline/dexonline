{extends "layout-admin.tpl"}

{block "title"}Tipuri de modele{/block}

{block "content"}
  {if $showAddForm}
    <div class="card mb-3">
      <div class="card-header">Adaugă un tip de model nou</div>
      <div class="card-body">

        <p>
          Notă: prin această interfață nu se pot crea tipuri de model
          canonice, ci doar redirectări la alte tipuri.
        </p>

        <form method="post">
          <input type="hidden" name="id" value="0">
          <div class="mb-3">
            <label class="form-label">cod</label>
            <input type="text" name="code" value="{$addModelType->code}" size="10" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">cod canonic</label>
            <select class="form-select" name="canonical">
              {foreach $canonicalModelTypes as $mt}
                <option value="{$mt->code}">{$mt->code}</option>
              {/foreach}
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">descriere</label>
            <input type="text" name="description" value="{$addModelType->description}" class="form-control">
          </div>

          <button class="btn btn-primary" type="submit" name="saveButton">
            {include "bits/icon.tpl" i=save}
            <u>s</u>alvează
          </button>

          <a class="btn btn-link" href="{Router::link('model/listTypes')}">renunță</a>

        </form>
      </div>
    </div>
  {/if}

  {if isset($editModelType)}
    <div class="card mb-3">
      <div class="card-header">Editează tipul de model {$editModelType->code}</div>
      <div class="card-body">

        <form method="post">
          <input type="hidden" name="id" value="{$editModelType->id}">

          <div class="mb-3">
            <label class="form-label">cod</label>
            <input type="text" value="{$editModelType->code}" disabled class="form-control">
          </div>

          {if $editModelType->code != $editModelType->canonical}
            <div class="mb-3">
              <label class="form-label">cod canonic</label>
              <input type="text" value="{$editModelType->canonical}" disabled class="form-control">
            </div>
          {/if}

          <div class="mb-3">
            <label class="form-label">descriere</label>
            <input type="text" name="description" value="{$editModelType->description}" class="form-control">
          </div>

          <button class="btn btn-primary" type="submit" name="saveButton">
            {include "bits/icon.tpl" i=save}
            <u>s</u>alvează
          </button>

          <a class="btn btn-link" href="{Router::link('model/listTypes')}">renunță</a>
        </form>
      </div>
    </div>
  {/if}

  <h3>Tipuri de model</h3>

  <table class="table table-sm">
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
        <td>{$lexemeCounts[$i]}</td>
        <td>
          <a class="btn btn-link btn-sm" href="?editId={$mt->id}">
            {include "bits/icon.tpl" i=edit}
          </a>
          {if $canDelete[$i]}
            <a class="btn btn-link btn-sm" href="?deleteId={$mt->id}">
              {include "bits/icon.tpl" i=delete}
            </a>
          {/if}
        </td>
      </tr>
    {/foreach}
  </table>

  {if !$showAddForm}
    <a class="btn btn-outline-secondary" href="?add=1">
      {include "bits/icon.tpl" i=add}
      adaugă un tip de model
    </a>
  {/if}
{/block}
