<div class="modal-dialog">
  <div class="modal-content">
    <form id="typoHtmlForm" method="post" action="" onsubmit="return submitTypoForm()">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Semnalează o greșeală</h4>
    </div>
    <div class="modal-body">
      <textarea class="form-control"
        id="typoTextarea" cols="40" rows="3"
        placeholder="descrieți problema aici..."></textarea>
      <input type="hidden" name="definitionId" value="{$definitionId}">

      <div id="typoDivNote">Note:</div>

      <ul>
        <li>Unele dicționare (de exemplu <em>Scriban</em>) sunt preluate cu grafia veche. Aceasta nu este o greșeală de tipar.</li>
        <li>
          În general, preluăm definițiile fără modificări, dar putem face comentarii pe marginea lor. Vă rugăm să nu ne semnalați greșeli semantice decât în
          situații evidente.
        </li>
      </ul>
    </div>
    <div class="modal-footer">
      <input class="btn btn-primary" type="submit" value="Trimite">
      <button class="btn btn-link" href="#" data-dismiss="modal">anulează</button>
    </div>
    </form>
  </div>
</div>
