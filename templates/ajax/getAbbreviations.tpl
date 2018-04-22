<div class="panel-admin">
  <div class="panel panel-default">
    <div class="panel-heading" id="panel-heading">
      <i class="glyphicon glyphicon-user"></i>
      {$modUser}
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
            <th data-column-id="commands" data-formatter="commands" data-sortable="false">Comenzi</th>
          </tr>
        </thead>
        <tbody>
          {foreach $results as $row}
            <tr id="{$row->id}">
              <td>
                <span class="label label-default">{$row->id}</span>
              </td>
              {* define the function *}
              {function name=prop}
                {foreach $props as $checked}
                  {$label = ($checked) ? 'success' : 'primary'}
                  {$icon = ($checked) ? 'ok' : 'minus'}
                  <td>
                    <label class="label label-{$label}">
                      <i class="glyphicon glyphicon-{$icon}" data-checked="{$checked}"></i>
                    </label>
                  </td>
                {/foreach}
              {/function}
              {* create an array of properties *}
              {$props = [$row->enforced, $row->ambiguous, $row->caseSensitive]}
              {* run the array through the function *}
              {call prop data=$props} 
              <td>{$row->short}</td>
              <td>{$row->internalRep}</td>
              <td>
                <div class="btn-group btn-group">
                  <button type="button" class="btn btn-xs btn-default" name="btn-edit" data-row-id="{$row->id}">
                    <i class="glyphicon glyphicon-edit"></i>
                  </button>
                  <button type="button" class="btn btn-xs btn-default" data-row-id="{$row->id}">
                    <i class="glyphicon glyphicon-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    {else}
      <p class="panel-body text-danger">
        Nu există abrevieri încărcate pentu dicționarul ales.
      </p>
    {/if} 
    <div class="panel-footer text-center clearfix">
      <span class="label label-default">Total abrevieri: {$results|count}</span>
    </div>

  </div>
</div>
{if $results|count  != 0}
  <div class="pull-right">
    <button type="button" class="btn btn-primary" id="command-add" data-source-id="{$sourceId}">
      <span class="glyphicon glyphicon-plus"></span> Adaugă
    </button>
  </div>    
{/if}