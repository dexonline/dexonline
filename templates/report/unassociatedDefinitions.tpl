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
      class="btn btn-outline-secondary"
      data-bs-toggle="modal"
      data-bs-target="#associateModal">
      asociază...
    </button>
  </div>

  <div class="modal fade" id="associateModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" role="form">
          <div class="modal-header">
            <h4 class="modal-title">Asociază definițiile cu...</h4>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close">
            </button>
          </div>

          <div class="modal-body">
            <input type="hidden" name="associateDefinitionIds" value="">
            <select id="associateEntryIds" name="associateEntryIds[]" class="form-select" multiple>
            </select>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="associateButton">
              asociază
            </button>
            <button type="button" class="btn btn-link" data-bs-dismiss="modal">renunță</button>
          </div>
        </form>
      </div>
    </div>
  </div>

{/block}
