{extends file="layout.tpl"}

{block name=title}Editare flexiuni{/block}

{block name=content}
  <h2>Editare flexiuni</h2>

  <form method="post" action="flexiuni">
    <table id="inflections" class="userTop">
      <thead>
        <tr>
          <th>Descriere</th>
          <th>Tip de model</th>
          <th>Ordinea inițială</th>
          <th>Șterge</th>
        </tr>
      </thead>

      <tfoot>  
        <tr>
          <td colspan="4">
            <input type="submit" name="submitButton" value="Salvează"/>
          </td>
        </tr>
      </tfoot>

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
            Adaugă: <input type="text" name="newDescription" value=""/>
          </td>
          <td>
            <select name="newModelType">
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
  </form>

  <b>Instrucțiuni:</b> Trageți de rânduri pentru a le reordona, apoi apăsați <i>Salvează</i>. Acest tabel enumeră setul cel mai cuprinzător de forme pe care le poate avea fiecare tip de model.
  Pentru fiecare model în parte, veți avea posibilitatea să specificați forme lipsă (de exemplu, pentru V666) sau forme cu mai multe valori (de exemplu, mai mult ca perfectul verbelor).
  Vi se permite ștergerea doar a acelor flexiuni care nu sunt folosite în niciun model (de obicei, cele nou create).

  <script>
   $(document).ready(function() {
     $("#inflections").tableDnD();
   });
  </script>
{/block}
