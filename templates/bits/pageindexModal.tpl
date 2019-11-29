{$sourceId=$sourceId|default:0}
<div id="edit_modal" class="modal fade" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="title">Editare index pagini</h4>
      </div>
      <form method="post" id="frm_edit">
        <div class="modal-body">
          <div id="deleted">
          <input type="hidden" id="action" name="action" value="">
          <input type="hidden" id="pageindexId" name="pageindexId" value="0">
          <input type="hidden" id="sourceId" name="sourceId" value="{$sourceId}">
          <div class="form-group">
            <label for="volume" class="control-label">Volum:</label>
            <input type="text" class="form-control" id="volume" name="volume" value=""/>
          </div>
          <div class="form-group">
            <label for="page" class="control-label">Pagină:</label>
            <input type="text" class="form-control" id="page" name="page" value=""/>
          </div>
          <div class="form-group">
            <label for="word" class="control-label">Cuvânt:</label>
            <input type="text" class="form-control" id="word" name="word" value=""/>
          </div>
          <div class="form-group">
            <label for="number" class="control-label">Indice/Exponent:</label>
            <input type="text" class="form-control" id="number" name="number" value=""/>
          </div>
          </div>
        </div>
        <div class="alert alert-warning" id="message" style="display: none">
        {* empty div for editAbbreviations.php messages *}
        </div>
        <div class="alert alert-warning alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
          Avertisment! În momentul editării greșite a unei valori, în formularul de căutare
          pagini scanate imaginea adecvată poate fi indisponibilă.
        </div>
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
