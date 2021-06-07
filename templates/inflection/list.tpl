{extends "layout-admin.tpl"}

{block "title"}Editare flexiuni{/block}

{block "content"}
  <h3>Editare flexiuni</h3>

  <div class="alert alert-info">
    <strong>Instrucțiuni:</strong> Trageți de icoana
    {include "bits/icon.tpl" i=drag_indicator}
    pentru a le reordona rîndurile, apoi apăsați <em>salvează</em>. Puteți
    șterge doar flexiunile nefolosite (de obicei, cele nou create).
  </div>

  <form method="post">
    <table id="inflections" class="table table-sm table-hover sortable">
      <thead>
        <tr>
          <th>Ordine</th>
          <th>Descriere</th>
          <th>Tip de model</th>
          <th>Ordinea inițială</th>
          <th>Șterge</th>
        </tr>
      </thead>

      <tbody>
        {foreach $inflections as $infl}
          <tr>
            <td>
              {include "bits/icon.tpl" i=drag_indicator class="drag-indicator"}
            </td>
            <td class="nick">
              <input type="hidden" name="inflectionIds[]" value="{$infl->id}">
              {$infl->description}
            </td>
            <td>{$infl->modelType}</td>
            <td>{$infl->rank}</td>
            <td>{if $infl->canDelete}<a href="?deleteInflectionId={$infl->id}">șterge</a>{/if}</td>
          </tr>
        {/foreach}
        <tr>
          <td></td>
          <td class="nick">
            <input type="text" name="newDescription" value="" class="form-control" placeholder="Adaugă">
          </td>
          <td>
            <select class="form-select" name="newModelType">
              {foreach $modelTypes as $mt}
                <option value="{$mt->code|escape}">{$mt->code|escape}</option>
              {/foreach}
            </select>
          </td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>

    <button type="submit" name="saveButton" class="btn btn-primary">
      {include "bits/icon.tpl" i=save}
      <u>s</u>alvează
    </button>

  </form>
{/block}
