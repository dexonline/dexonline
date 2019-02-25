{extends "layout-admin.tpl"}

{block "title"}Definiții neasociate{/block}

{block "content"}

  <h3>{$searchResults|count} definiții neasociate</h3>

  {foreach $searchResults as $row}
    {include "bits/definition.tpl"
      showDate=1
      showDeleteLink=1
      showStatus=1
      showSelectCheckbox=1
    }
  {/foreach}

  <div>
    <button type="button"
      class="btn btn-default"
      data-toggle="modal"
      data-target="#associateModal">
      <i class="glyphicon glyphicon-resize-small"></i>
      asociază...
    </button>
  </div>

  <div class="modal fade" id="associateModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" role="form">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Asociază definițiile cu...</h4>
          </div>

          <div class="modal-body">
            <input type="hidden" name="associateDefinitionIds" value="">
            <select id="associateEntryIds" name="associateEntryIds[]" class="form-control" multiple>
            </select>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="associateButton">
              <i class="glyphicon glyphicon-resize-small"></i>
              asociază
            </button>
            <button type="button" class="btn btn-link" data-dismiss="modal">renunță</button>
          </div>
        </form>
      </div>
    </div>
  </div>

{/block}
