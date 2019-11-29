<form class="form-inline" action="{Router::link('model/dispatch')}">
  <div class="form-group">
    <span data-model-dropdown>
      {include "bits/modelTypeDropdown.tpl"}
      {include "bits/modelNumberDropdown.tpl"}
    </span>
    <div class="btn-group">
      <button type="submit" class="btn btn-default" name="showLexemes">
        arată toate lexemele
      </button>
      <button type="submit" class="btn btn-default" name="editModel">
        <i class="glyphicon glyphicon-pencil"></i>
        editează
      </button>
      <button type="submit" class="btn btn-default" name="cloneModel">
        <i class="glyphicon glyphicon-duplicate"></i>
        clonează
      </button>
      <button type="submit" class="btn btn-danger" name="deleteModel">
        <i class="glyphicon glyphicon-trash"></i>
        șterge
      </button>
    </div>
  </div>
</form>

<div class="voffset2"></div>

<p>
  <a href="{$mergeToolLink}">unificare plural-singular</a>

  <span class="text-muted">
    pentru familiile de plante și animale și pentru alte lexeme care apar
    cu restricția „P” într-o sursă, dar fără restricții în altă sursă.
  </span>
</p>

<p>
  <a href="{$bulkLabelSelectSuffixLink}">
    etichetare în masă a lexemelor
  </a>

  <span class="text-muted">
    pe baza sufixului
  </span>
</p>