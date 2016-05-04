{extends file="layout.tpl"}

{block name=title}Verificarea acurateței{/block}

{block name=content}
  <h2>Verificarea acurateței</h2>

  {if $projects}
    <h3>Proiectele mele</h3>

    <form method="get">
      {include "bits/dropdown.tpl" name="id" data=$projects}

      <button type="submit">deschide</button>

      <button type="submit" id="deleteButton" name="deleteButton" value="1">
        șterge
      </button>
    </form>
  {/if}

  <h3>Creează un proiect nou</h3>

  <form method="post">
    <table class="minimalistTable">
      <tr>
        <td>nume</td>
        <td><input type="text" name="name" value="{$p->name}"></td>
      </tr>
      <tr>
        <td>utilizator</td>
        <td><input type="text" id="userId" name="userId" value="{$p->userId}"></td>
      </tr>
      <tr>
        <td>sursă (opțional)</td>
        <td>
          {include "bits/sourceDropDown.tpl" name="sourceId" src_selected=$p->sourceId}
        </td>
      </tr>
      <tr>
        <td>dată de început (opțional)</td>
        <td><input type="text" name="startDate" value="{$p->startDate}" placeholder="AAAA-LL-ZZ"></td>
      </tr>
      <tr>
        <td>dată de sfârșit (opțional)</td>
        <td><input type="text" name="endDate" value="{$p->endDate}" placeholder="AAAA-LL-ZZ"></td>
      </tr>
      <tr>
        <td>metodă</td>
        <td>
          {include "bits/dropdown.tpl" name="method" data=AccuracyProject::getMethodNames() selected=$p->method}
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <button type="submit" name="submitButton" value="1">
            creează
          </button>
        </td>
      </tr>
    </table>

  </form>
{/block}
