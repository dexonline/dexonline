{$sourceId=$sourceId|default:0}
<div id="edit_modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="title">Editare abreviere</h4>
      </div>
      <form method="post" id="frm_edit">
        <div class="modal-body">
          <div id="deleted">
          <input type="hidden" id="action" name="action" value="">
          <input type="hidden" id="abbrevId" name="abbrevId" value="0">
          <input type="hidden" id="sourceId" name="sourceId" value="{$sourceId}">
          <div class="form-group">
            <div class="row">
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="enforced" name="enforced"/>
                <label for="enforced">Impus</label>
              </div>
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="ambiguous" name="ambiguous"/>
                <label for="ambiguous" class="control-label">Ambiguu</label>
              </div>
              <div class="col-xs-4">
                <input type="checkbox" class="col col-xs-1" id="caseSensitive" name="caseSensitive"/>
                <label for="caseSensitive" class="control-label">Diferențiere aA</label>
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
        </div>
        {* empty div for editAbbreviations.php messages *}
        <div class="alert alert-warning" id="message" style="display: none"></div>
        <div class="modal-footer">
          <div class="row">
            <div class="col-sm-4 col-sm-offset-4">
              <button type="button" class="btn btn-primary btn-block" data-dismiss="modal">
                <i class="glyphicon glyphicon-remove"></i>
                abandonează
              </button>
            </div>
            <div class="col-sm-4" id="btn-save">
              <button class="btn btn-success btn-block ld-ext-left commands" name="saveButton">
                <div class="ld ld-ring ld-spin-fast"></div>
                <i class="glyphicon glyphicon-floppy-disk"></i>
                <u>s</u>alvează
              </button>
            </div>
            <div class="col-sm-4" id="btn-delete">
              <div class="btn btn-danger btn-block ld-ext-left commands">
                <div class="ld ld-ring ld-spin-fast"></div>
                <i class="glyphicon glyphicon-trash"></i>
                șterge
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
