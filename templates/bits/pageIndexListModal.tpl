{$sourceId=$sourceId|default:0}
<div id="edit_modal" class="modal fade" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="title">Editare index</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="frm_edit">
        <div class="modal-body">
          <div id="deleted">
            <input type="hidden" id="action" name="action" value="">
            <input type="hidden" id="pageIndexId" name="pageIndexId" value="0">
            <input type="hidden" id="sourceId" name="sourceId" value="{$sourceId}">
            <div class="mb-3">
              <label for="volume" class="form-label">Volum:</label>
              <input type="text" class="form-control" id="volume" name="volume" value="">
            </div>
            <div class="mb-3">
              <label for="page" class="form-label">Pagină:</label>
              <input type="text" class="form-control" id="page" name="page" value="">
            </div>
            <div class="mb-3">
              <label for="word" class="form-label">Intrare:</label>
              <input type="text" class="form-control" id="word" name="word" value="">
            </div>
            <div class="mb-3">
              <label for="number" class="form-label">Număr definiție:</label>
              <input type="text" class="form-control" id="number" name="number" value="">
            </div>
          </div>
        </div>
        {* empty div for editPageIndex.php messages *}
        <div id="message">
          {notice type="error"}{/notice}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">
            renunță
          </button>
          <button
            id="btn-save"
            class="btn btn-primary commands"
            name="saveButton">
            {include "bits/icon.tpl" i=save}
            <u>s</u>alvează
          </button>
          <div
            id="btn-delete"
            class="btn btn-danger commands">
            {include "bits/icon.tpl" i=delete}
            șterge
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
