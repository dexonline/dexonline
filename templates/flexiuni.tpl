{extends "layout-admin.tpl"}

{block "title"}Editare flexiuni{/block}

{block "content"}
  <h3>Editare flexiuni</h3>

  <p class="alert alert-info">
    <strong>Instrucțiuni:</strong> Trageți de rânduri pentru a le reordona, apoi apăsați
    <em>Salvează</em>. Puteți șterge doar flexiunile nefolosite (de obicei, cele nou create).
  </p>



  <form method="post" action="flexiuni">
    <table id="inflections" class="table table-bordered table-hover table-condensed table-striped">
      <thead>
        <tr>
          <th>Descriere</th>
          <th>Tip de model</th>
          <th>Ordinea inițială</th>
          <th>Șterge</th>
        </tr>
      </thead>

      <tbody>
        {foreach $inflections as $infl}
          <tr>
            <td class="nick">
              <input type="hidden" name="inflectionIds[]" value="{$infl->id}"/>
              {$infl->description}
            </td>
            <td>{$infl->modelType}</td>
            <td>{$infl->rank}</td>
            <td>{if $infl->canDelete}<a href="?deleteInflectionId={$infl->id}">șterge</a>{/if}</td>
          </tr>
        {/foreach}
        <tr>
          <td class="nick">
            <input type="text" name="newDescription" value="" class="form-control" placeholder="Adaugă" />
          </td>
          <td>
            <select class="form-control" name="newModelType">
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

    <button type="submit" name="saveButton" class="btn btn-success">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>

  </form>

  <script>
   $(document).ready(function() {
     $("#inflections").tableDnD();
   });
  </script>
{/block}
