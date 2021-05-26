{$sourceId=$sourceId|default:0}
<div id="edit_modal" class="modal fade" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="title">Editare abreviere</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="frm_edit">
        <div class="modal-body">
          <input type="hidden" id="action" name="action" value="">
          <input type="hidden" id="abbrevId" name="abbrevId" value="0">
          <input type="hidden" id="sourceId" name="sourceId" value="{$sourceId}">
          <div class="form-group">
            <div class="row">
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="enforced" name="enforced">
                <label for="enforced">Impus</label>
              </div>
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="ambiguous" name="ambiguous">
                <label for="ambiguous" class="control-label">Ambiguu</label>
              </div>
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="caseSensitive" name="caseSensitive">
                <label for="caseSensitive" class="control-label">Diferențiere aA</label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="short" class="control-label">Abreviere:</label>
            <input type="text" class="form-control" id="short" name="short" value="x">
          </div>
          <div class="form-group">
            <label for="internalRep" class="control-label">Detalierea abrevierii:</label>
            <input type="text" class="form-control" id="internalRep" name="internalRep" value="">
          </div>
        </div>
        {* empty div for editAbbreviations.php messages *}
        <div class="alert alert-warning" id="message" style="display: none"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">
            renunță
          </button>
          <button
            id="btn-save"
            class="btn btn-success commands"
            type="button"
            name="saveButton">
            {include "bits/icon.tpl" i=save}
            <u>s</u>alvează
          </button>
          <button
            id="btn-delete"
            class="btn btn-danger commands"
            type="button">
            {include "bits/icon.tpl" i=delete}
            șterge
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
