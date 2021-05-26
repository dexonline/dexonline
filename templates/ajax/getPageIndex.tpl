<div class="card">
  <div class="card-header">
    Lista indecșilor de pagină
  </div>
  {if count($results)}
    <table id="table-page-index" class="table table mb-0">
      <thead>
        <tr>
          <th>Id</th>
          <th>Vol.</th>
          <th>Pag.</th>
          <th>Cuv.</th>
          <th>Nr. def.</th>
          {if User::can(User::PRIV_ADMIN)}
            <th>Comenzi</th>
          {/if}
        </tr>
      </thead>
      <tbody>
        {foreach $results as $row}
          {include "bits/pageIndexRow.tpl"}
        {/foreach}
      </tbody>
    </table>
  {else}
    <div class="card-body text-danger">
      Nu există indecși de pagină pentru dicționarul ales.
    </div>
  {/if}
  <div class="card-footer text-center">
    Total indecși: <span id="pageIndexCount">{$results|count}</span>
  </div>
</div>

{if count($results) && User::can(User::PRIV_ADMIN)}
  <div class="d-flex justify-content-end mt-2">
    <button type="button" class="btn btn-primary" id="command-add" data-source-id="{$sourceId}">
      {include "bits/icon.tpl" i=add}
    </button>
  </div>
{/if}
