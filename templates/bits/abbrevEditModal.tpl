{$sourceId=$sourceId|default:0}
<div id="edit_modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Editare abreviere</h4>
      </div>
      <form method="post" id="frm_edit">
        <div class="modal-body">
          <input type="hidden" name="action" id="action" value="add">
          <input type="hidden" name="abbrevId" value="0">
          <input type="hidden" name="sourceId" value="{$sourceId}">
          <div class="form-group">
            <div class="row">
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="enforced" name="enforced"/>
                <label for="enforced">Impus:</label>
              </div>
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="ambiguous" name="ambiguous"/>
                <label for="ambiguous" class="control-label">Ambiguu:</label>
              </div>
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="caseSensitive" name="caseSensitive"/>
                <label for="caseSensitive" class="control-label">CS:</label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="short" class="control-label">Abreviere:</label>
            <input type="text" class="form-control" id="short" name="short" value="x"/>
          </div>
          <div class="form-group">
            <label for="internalRep" class="control-label">Detalierea abrevierii:</label>
            <input type="text" class="form-control" id="internalRep" name="internalRep" value=""/>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">
            <i class="glyphicon glyphicon-remove"></i>
            abandonează
          </button>
          <button type="button" id="btn-save" class="btn btn-success">
            <i class="glyphicon glyphicon-floppy-disk"></i>
            <u>s</u>alvează 
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
