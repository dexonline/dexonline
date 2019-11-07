<div class="modal fade" tabindex="-1" role="dialog" id="modal-charmap">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Inserează un șir de glife</h4>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="row-modal" data-role="buttons"></div>
          <div class="row-modal">
            <a role="button" id="editButton"
               class="btn btn-info collapsed" data-toggle="collapse"
               href="#editArea" aria-expanded="false"
               aria-controls="editArea">
              <span class="if-collapsed">Editează</span>
              <span class="if-not-collapsed">Închide</span>
            </a>
            <a role="button" id="textButton"
               class="btn btn-info collapsed pull-right" data-toggle="collapse"
               href="#charsArea" aria-expanded="false"
               aria-controls="charsArea">
              <span class="if-collapsed">mai multe glife</span>
              <span class="if-not-collapsed">o singură glifă</span>
            </a>
          </div>
          <div class="row-modal">
            <div id="editArea" class="collapse" aria-expanded="false">
              <textarea id="editBox" class="form-control" rows="10" title="Glife"></textarea>
              <div class="col">
                <div class="btn-group btn-group pull-right">
                  <button type="submit" id="saveButton" class="btn btn-success"
                          title="salvează lista de glife">
                    <i class="glyphicon glyphicon-floppy-disk"></i>
                    salvează
                  </button>
                  <button type="button" id="resetButton" class="btn btn-danger"
                          title="resetează lista la valorile inițiale">
                    <i class="glyphicon glyphicon-refresh"></i>
                    resetează
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="row-modal">
            <div id="charsArea" class="collapse" aria-expanded="false">
              <div class="input-group">
                <div class="form-group has-feedback">
                  <input type="text" class="form-control ui-autocomplete-input"
                         name="charmapGlyphs" placeholder="adaugă glife"
                         id="charsText" value="" maxlength="400" autocomplete="off">
                  <span id="charsClear" class="glyphicon glyphicon-remove form-control-feedback"> </span>
                  <span id="charsSend" class="glyphicon glyphicon-ok form-control-feedback collapse"> </span>
                </div>
                <span class="input-group-btn">
                  <button type="button" value="trimite" id="charsInsert" class="btn btn-primary" data-dismiss="modal">
                    <span class="glyphicon glyphicon-arrow-down"></span> Inserează
                  </button>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer text-muted text-left">
        Aici puteți salva glife (semne) pe care le folosiți des și care sunt greu de tastat.</br>
        Apăsați butonul <em>Editează</em> și personalizați lista, după modelul
        <strong>m;M[;detaliere]</strong> pe câte o linie.</br>
        Puteți chiar să salvați șiruri mai lungi de un caracter, dacă vă ajută.
      </div>
    </div> <!-- /.modal-content -->
  </div> <!-- /.modal-dialog -->
</div>
<script>
  Charmap.init('#modal-charmap');
</script>
