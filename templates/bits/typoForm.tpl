{if $skinVariables.typo}
  <div id="typoModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="typoHtmlForm" method="post" onsubmit="return submitTypoForm()">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Semnalează o greșeală</h4>
          </div>

          <div class="modal-body">
            <textarea class="form-control"
                      id="typoTextarea" cols="40" rows="3"
                      placeholder="descrieți problema aici..."></textarea>
            <input type="hidden" name="definitionId" value="">

            <div>Note:</div>

            <ul>
              <li>Unele dicționare (de exemplu <em>Scriban</em>) sunt preluate cu grafia veche. Aceasta nu este o greșeală de tipar.</li>
              <li>
                În general, preluăm definițiile fără modificări, dar putem face comentarii pe marginea lor. Vă rugăm să nu ne semnalați greșeli semantice decât în
                situații evidente.
              </li>
            </ul>
          </div>

          <div class="modal-footer">
            <button class="btn btn-primary" id="typoSubmit" type="submit">trimite</button>
            <button class="btn btn-link" data-dismiss="modal">anulează</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {* Confirmation modal *}
  <div id="typoConfModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Semnalează o greșeală</h4>
        </div>

        <div class="modal-body">
          Vă mulțumim pentru semnalare!
        </div>

        <div class="modal-footer">
          <button class="btn btn-link" data-dismiss="modal">închide</button>
        </div>
      </div>
    </div>
  </div>
{/if}
