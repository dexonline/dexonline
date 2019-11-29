<input type="hidden" name="id" value="{$lexeme->id}">
<div class="checkbox">
  <label>
    <input type="checkbox" name="cloneEntries[1][]" checked>
    copiază intrările unde este lexem principal
  </label>
</div>
<div class="checkbox">
  <label>
    <input type="checkbox" name="cloneEntries[0][]" checked>
    copiază intrările unde este lexem variantă
  </label>
</div>
<div class="checkbox">
  <label>
    <input type="checkbox" name="cloneInflectedForms">
    copiază formele flexionare
  </label>
  <span class="small text-muted"> (dacă e debifat nu copiază nici modelul)</span>
</div>
<div class="checkbox">
  <label>
    <input type="checkbox" name="cloneTags">
    copiază etichetele
  </label>
</div>
<div class="checkbox">
  <label>
    <input type="checkbox" name="cloneSources" checked>
    copiază sursele
  </label>
</div>
