{extends "layout.tpl"}

{block "title"}Etichete pe istoria definiției {$def->lexicon}{/block}

{block "content"}
  <h3>
    Etichete pe istoria definiției
    <a href="definitie/{$def->id}">{$def->lexicon}</a>
  </h3>

  {include "bits/definitionChange.tpl" c=$change tagLink=false}

  <form>
    <input type="hidden" name="id" value="{$dv->id}">

    <div class="mb-3">
      <label class="form-label">etichete</label>

      <select name="tagIds[]" class="form-select select2Tags" multiple>
        {foreach $change.tags as $t}
          <option value="{$t->id}" selected></option>
        {/foreach}
      </select>
    </div>

    <div class="mb-3">
      {include "bs/checkbox.tpl"
        name=applyToDefinition
        label='aplică noile etichete și pe definiția însăși'
        checked=true
        help='Doar etichetele adăugate sunt preluate pe definiție. Dacă doriți să ștergeți etichete de pe definiție, trebuie să le ștergeți manual.'}
    </div>

    <div>
      <button type="submit" class="btn btn-primary" name="saveButton">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>

      <a class="btn btn-link" href="{Router::link('definition/history')}?id={$def->id}">
        {include "bits/icon.tpl" i=arrow_back}
        înapoi la istoria definiției
      </a>
    </div>

  </form>
{/block}
