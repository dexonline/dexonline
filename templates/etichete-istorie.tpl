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

    <div class="form-group">
      <label>etichete</label>

      <select name="tagIds[]" class="form-control select2Tags" multiple>
        {foreach $change.tags as $t}
          <option value="{$t->id}" selected></option>
        {/foreach}
      </select>

      <div class="checkbox">
        <label>
          <input type="checkbox"
                 name="applyToDefinition"
                 value="1"
                 checked>
          aplică noile etichete și pe definiția însăși
        </label>
        <p class="help-block">
          Doar etichetele adăugate sunt preluate pe definiție. Dacă doriți să ștergeți
          etichete de pe definiție, trebuie să le ștergeți manual.
        </p>
      </div>
    </div>

    <div>
      <button type="submit" class="btn btn-success" name="saveButton">
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>

      <a class="btn btn-default" href="istoria-definitiei.php?id={$def->id}">
        <i class="glyphicon glyphicon-arrow-left"></i>
        înapoi la istoria definiției
      </a>
    </div>

  </form>
{/block}
