<input type="hidden" name="id" value="{$lexeme->id}">
<div class="form-check">
  <label class="form-check-label">
    <input type="checkbox" class="form-check-input" name="cloneEntries[1][]" checked>
    copiază intrările unde este lexem principal
  </label>
</div>
<div class="form-check">
  <label class="form-check-label">
    <input type="checkbox" class="form-check-input" name="cloneEntries[0][]" checked>
    copiază intrările unde este lexem variantă
  </label>
</div>
<div class="form-check">
  <label class="form-check-label">
    <input type="checkbox" class="form-check-input" name="cloneInflectedForms" checked>
    copiază formele flexionare
  </label>
  <span class="small text-muted"> (dacă e debifat nu copiază nici modelul)</span>
</div>
<div class="form-check">
  <label class="form-check-label">
    <input type="checkbox" class="form-check-input" name="cloneTags" checked>
    copiază etichetele
  </label>
</div>
<div class="form-check">
  <label class="form-check-label">
    <input type="checkbox" class="form-check-input" name="cloneSources" checked>
    copiază sursele
  </label>
</div>
