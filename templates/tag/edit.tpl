{extends "layout-admin.tpl"}

{block "title"}Eticheta {$t->value}{/block}

{block "content"}
  {if $t->id}
    <h3>Eticheta [{$t->value}]</h3>
  {else}
    <h3>Adaugă o etichetă</h3>
  {/if}

  {include "bits/tagAncestors.tpl" tag=$t}

  <form method="post" class="mt-3">
    <input type="hidden" name="id" value="{$t->id}">

    <div class="row">

      <div class="col-md-6">
        <div class="row mb-3">
          <label for="value" class="col-md-2 col-form-label">
            nume
          </label>
          <div class="col-md-10">
            <div>
              <input type="text"
                class="form-control {if isset($errors.value)}is-invalid{/if}"
                id="value"
                name="value"
                value="{$t->value}">
              {include "bits/fieldErrors.tpl" errors=$errors.value|default:null}
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <label for="tooltip" class="col-md-2 col-form-label">
            detalii
          </label>
          <div class="col-md-10">
            <div>
              <input type="text"
                class="form-control"
                id="tooltip"
                name="tooltip"
                value="{$t->tooltip}"
                placeholder="opționale; apar la survolarea cu mouse-ul">
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <label for="parentId" class="col-md-2 col-form-label">
            părinte
          </label>
          <div class="col-md-10">
            <div>
              <select
                id="parentId"
                name="parentId"
                class="form-select {if isset($errors.parentId)}is-invalid{/if}">
                {if $t->parentId}
                  <option value="{$t->parentId}" selected></option>
                {/if}
              </select>
              {include "bits/fieldErrors.tpl" errors=$errors.parentId|default:null}

              <div class="mt-2">
                {include "bs/checkbox.tpl"
                  name=public
                  label='publică'
                  checked=$t->public
                  help='Dacă nu este publică, eticheta este vizibilă doar pentru utilizatorii privilegiați.'}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">

        <div class="row mb-3"">
          <label for="color" class="col-md-2 col-form-label">
            culoare
          </label>
          <div class="col-md-10">
            <input
              type="color"
              class="form-control"
              id="color"
              name="color"
              value="{$t->getColor()}">
            {include "bits/frequentColors.tpl"
              colors=$frequentColors.color
              target="#color"}
          </div>
        </div>

        <div class="row mb-3"">
          <label for="background" class="col-md-2 col-form-label">
            fundal
          </label>
          <div class="col-md-10">
            <input
              type="color"
              class="form-control"
              id="background"
              name="background"
              value="{$t->getBackground()}">
            {include "bits/frequentColors.tpl"
              colors=$frequentColors.background
              target="#background"}
          </div>
        </div>

        <div class="row mb-3">
          <label for="icon" class="col-md-2 col-form-label">
            iconiță
          </label>
          <div class="col-md-10">
            <div class="input-group">
              {if $t->icon}
                <span class="input-group-text">
                  {include "bits/icon.tpl" i=$t->icon}
                </span>
              {/if}
              <input type="text"
                class="form-control"
                id="icon"
                name="icon"
                value="{$t->icon}">
            </div>

            {include "bs/checkbox.tpl"
              name=iconOnly
              label='arată doar iconița fără text'
              checked=$t->iconOnly}

            <div class="form-text">
              Opțional, un nume de <a href="https://fonts.google.com/icons">
              iconiță</a>. Folosiți litere mici și puneți <code>_</code> în loc de spații, de exemplu
              <code>home</code> sau <code>check_circle</code>.
            </div>

          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary" name="saveButton">
      {include "bits/icon.tpl" i=save}
      <u>s</u>alvează
    </button>

    <a class="btn btn-light" href="{Router::link('tag/list')}">
      {include "bits/icon.tpl" i=arrow_back}
      înapoi la lista de etichete
    </a>

    <a class="btn btn-link" href="{if $t->id}?id={$t->id}{/if}">
      renunță
    </a>

    <button type="submit"
      name="deleteButton"
      class="btn btn-danger float-end"
      {if !$canDelete}
      disabled
      title="Nu puteți șterge eticheta deoarece (1) are descendenți sau (2) este folosită."
      {/if}
    >
      {include "bits/icon.tpl" i=delete}
      șterge
    </button>
  </form>

  {if count($children)}
    <h3>Descendenți direcți</h3>

    {foreach $children as $c}
      {include "bits/tag.tpl" t=$c link=true}
    {/foreach}
  {/if}

  {if count($homonyms)}
    <h3>Ononime</h3>

    {foreach $homonyms as $h}
      <div class="voffset">
        {include "bits/tagAncestors.tpl" tag=$h}
      </div>
    {/foreach}
  {/if}

  {if count($lexemes)}
    <h3 class="mt-3">
      Lexeme asociate
      {if $lexemeCount > count($lexemes)}
        ({count($lexemes)} din {$lexemeCount} afișate)
      {else}
        ({count($lexemes)})
      {/if}
    </h3>

    {include "bits/lexemeList.tpl"}
  {/if}

  {if count($meanings)}
    <h3 class="mt-3">
      Sensuri asociate
      {if $meaningCount > count($meanings)}
        ({count($meanings)} din {$meaningCount} afișate)
      {else}
        ({count($meanings)})
      {/if}
    </h3>

    <table class="table">
      <thead>
        <tr>
          <th>arbore</th>
          <th>sens</th>
        </tr>
      </thead>

      <tbody>
        {foreach $meanings as $m}
          <tr>
            <td>
              <a href="{Router::link('tree/edit')}?id={$m->getTree()->id}">
                {$m->getTree()->description}
              </a>
            </td>
            <td>
              <strong>{$m->breadcrumb}</strong>
              {HtmlConverter::convert($m)}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}

  {if count($searchResults)}
    <h3>
      Definiții asociate
      {if $defCount > count($searchResults)}
        ({count($searchResults)} din {$defCount} afișate)
      {else}
        ({count($searchResults)})
      {/if}
    </h3>

    {foreach $searchResults as $row}
      {include "bits/definition.tpl"
        showDropup=0
        showStatus=1}
    {/foreach}
  {/if}

{/block}
