{extends file="layout.tpl"}

{block name=title}Editare flexiuni{/block}

{block name=banner}{/block}
{block name=search}{/block}

{block name=content}
  <h2>Editare flexiuni</h2>

  <p class="alert alert-info">
    <strong>Instrucțiuni:</strong> Trageți de rânduri pentru a le reordona, apoi apăsați <em>Salvează</em>.
    Acest tabel enumeră setul cel mai cuprinzător de forme pe care le poate avea fiecare tip de model.
    Pentru fiecare model în parte, veți avea posibilitatea să specificați forme lipsă (de exemplu, pentru V666) sau forme cu mai multe valori (de exemplu, mai mult ca perfectul verbelor).
    Vi se permite ștergerea doar a acelor flexiuni care nu sunt folosite în niciun model (de obicei, cele nou create).
  </p>



  <form method="post" action="flexiuni">
    <table id="inflections" class="table-bordered table-hover table-condensed table-striped col-sm-12">
      <thead>
        <tr>
          <th>Descriere</th>
          <th>Tip de model</th>
          <th>Ordinea inițială</th>
          <th>Șterge</th>
        </tr>
      </thead>

      <tbody>
        {foreach from=$inflections item=infl}
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
              {foreach from=$modelTypes item=mt}
                <option value="{$mt->code|escape}">{$mt->code|escape}</option>
              {/foreach}
            </select>
          </td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>

    <div class="clearfix"></div>
    <br />
    <input type="submit" name="submitButton" value="Salvează" class="btn btn-primary" />

  </form>

  <script>
   $(document).ready(function() {
     $("#inflections").tableDnD();
   });
  </script>
{/block}
