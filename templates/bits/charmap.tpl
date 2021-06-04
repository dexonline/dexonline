<div class="modal fade" tabindex="-1" role="dialog" id="modal-charmap">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Inserează un șir de glife</h4>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close">
        </button>
      </div>

      <div class="modal-body">
        <div data-role="buttons"></div>
        <div class="d-flex justify-content-between my-3">
          <a role="button"
            id="editButton"
            class="btn btn-info collapsed"
            data-bs-toggle="collapse"
            href="#editArea"
            aria-expanded="false"
            aria-controls="editArea">
            <span class="if-collapsed">editează</span>
            <span class="if-not-collapsed">închide</span>
          </a>
          <a role="button"
            id="textButton"
            class="btn btn-info collapsed"
            data-bs-toggle="collapse"
            href="#charsArea"
            aria-expanded="false"
            aria-controls="charsArea">
            <span class="if-collapsed">mai multe glife</span>
            <span class="if-not-collapsed">o singură glifă</span>
          </a>
        </div>

        <div id="editArea" class="collapse" aria-expanded="false">
          <textarea id="editBox" class="form-control" rows="10"></textarea>
          <div class="d-flex justify-content-end">
            <button type="submit" id="saveButton" class="btn btn-primary"
              title="salvează lista de glife">
              {include "bits/icon.tpl" i=save}
              salvează
            </button>
            <button type="button" id="resetButton" class="btn btn-link ms-1"
              title="resetează lista la valorile inițiale">
              {include "bits/icon.tpl" i=refresh}
              resetează
            </button>
          </div>
        </div>

        <div id="charsArea" class="collapse" aria-expanded="false">
          <div class="input-group">
            <input
              type="text"
              class="form-control"
              placeholder="adaugă glife"
              id="charsText"
              maxlength="400"
              autocomplete="off">
            <button
              type="button"
              value="trimite"
              id="charsInsert"
              class="btn btn-primary"
              data-bs-dismiss="modal">
              {include "bits/icon.tpl" i=done}
              inserează
            </button>
          </div>
        </div>
      </div>

      <div class="modal-footer d-block text-muted">
        Aici puteți salva glife (semne) pe care le folosiți des și care sunt
        greu de tastat. Apăsați butonul <em>Editează</em> și personalizați
        lista, după modelul <strong>m;M[;detaliere]</strong> pe câte o linie.
        Puteți chiar să salvați șiruri mai lungi de un caracter, dacă vă
        ajută.
      </div>
    </div> <!-- /.modal-content -->
  </div> <!-- /.modal-dialog -->
</div>
<script>
  Charmap.init('#modal-charmap');
</script>
