<div class="card">
  <div class="card-header">
    Lista abrevierilor
  </div>
  {if count($results)}
    <table id="table-abbrevs" class="table mb-0">
      <thead>
        <tr>
          <th>Id</th>
          <th>Imp.</th>
          <th>Amb.</th>
          <th>CS</th>
          <th>Abreviere</th>
          <th>Detalierea abrevierii</th>
          {if User::can(User::PRIV_ADMIN)}
            <th>Comenzi</th>
          {/if}
        </tr>
      </thead>
      <tbody>
        {foreach $results as $row}
          {include "bits/abbrevRow.tpl"}
        {/foreach}
      </tbody>
    </table>
  {else}
    <div class="card-body text-danger">
      Nu există abrevieri încărcate pentru dicționarul ales.
    </div>
  {/if}
  <div class="card-footer text-center">
    Total abrevieri: <span id="abbrevCount">{$results|count}</span>
  </div>
</div>

{if count($results) && User::can(User::PRIV_ADMIN)}
  <div class="d-flex justify-content-end mt-2">
    <button type="button" class="btn btn-primary" id="command-add" data-source-id="{$sourceId}">
      {include "bits/icon.tpl" i=add}
      adaugă
    </button>
  </div>
{/if}
