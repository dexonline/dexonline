<div class="panel-admin">
  <div class="panel panel-default">
    <div class="panel-heading" id="panel-heading">
      Lista abrevierilor
    </div>
    {if $results|count  != 0}
      <table id="table-abbrevs" class="table table-condensed table-striped table-bordered">
        <thead>
          <tr>
            <th data-column-id="id" data-type="numeric" data-identifier="true">Id</th>
            <th data-column-id="enforced">Imp.</th>
            <th data-column-id="ambiguous">Amb.</th>
            <th data-column-id="caseSensitive">CS</th>
            <th data-column-id="short">Abreviere</th>
            <th data-column-id="internalRep">Detalierea abrevierii</th>
            {if User::can(User::PRIV_ADMIN)}
            <th data-column-id="commands" data-formatter="commands" data-sortable="false">Comenzi</th>
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
      <p class="panel-body text-danger">
        Nu există abrevieri încărcate pentu dicționarul ales.
      </p>
    {/if}
    <div class="panel-footer text-center clearfix">
      <span class="label label-default">Total abrevieri: <span id="abbrevCount">{$results|count}</span></span>
    </div>
  </div>
</div>
{if $results|count  != 0 && User::can(User::PRIV_ADMIN)}
  <div class="pull-right">
    <button type="button" class="btn btn-primary" id="command-add" data-source-id="{$sourceId}">
      <span class="glyphicon glyphicon-plus"></span> Adaugă
    </button>
  </div>
{/if}
