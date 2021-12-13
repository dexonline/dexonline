{extends "layout-admin.tpl"}

{block "title"}
  {if $src->id}
    Editare sursă {$src->shortName}
  {else}
    Adăugare sursă
  {/if}
{/block}

{block "content"}

  <div class="card mb-3">
    <div class="card-header">
      {if $src->name}Editare sursă: {$src->name}{else}Adăugare sursă{/if}
    </div>

    <div class="card-body">

      <form method="post">
        <input type="hidden" name="id" value="{$src->id}">

        <div class="row">

          <div class="col-md-6">

            {include "bs/field.tpl" label='nume' name='name' value=$src->name}

            {include "bs/field.tpl"
              help='Numele sursei prezentat după fiecare definiție.'
              label='nume scurt'
              name='shortName'
              value=$src->shortName}

            {capture "help"}
            Numele care apare în URL la căutarea într-o anumită sursă, cum ar fi
            https://dexonline.ro/definitie-<strong>der</strong>/copil
            {/capture}
            {include "bs/field.tpl"
              help=$smarty.capture.help
              label='nume URL'
              name='urlName'
              value=$src->urlName}

            {include "bs/field.tpl" label='autor' name='author' value=$src->author}
            {include "bs/field.tpl" label='editură' name='publisher' value=$src->publisher}
            {include "bs/field.tpl" label='an' name='year' value=$src->year}

            <div class="mb-2">
              <label class="form-label">tipul sursei</label>
              <select class="form-select" name="sourceTypeId">
                <option value="0">Fără categorie</option>
                {foreach $sourceTypes as $st}
                  <option value="{$st->id}" {if $src->sourceTypeId == $st->id}selected{/if}>
                    {$st->name}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">managerul dicționarului</label>
              <select class="form-select" name="managerId">
                <option value="0">Fără moderator</option>
                {foreach $managers as $manager}
                  <option value="{$manager->id}" {if $src->managerId == $manager->id}selected{/if}>
                    {$manager->name}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">tipul importului</label>
              <select class="form-select" name="importType">
                {foreach Source::IMPORT_TYPE_LABELS as $importType => $label}
                  <option value="{$importType}" {if $src->importType == $importType}selected{/if}>
                    {$label}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">reforma ortografică</label>
              <select class="form-select" name="reformId">
                <option value="0">Fără categorie</option>
                {foreach $reforms as $reform}
                  <option value="{$reform->id}" {if $src->reformId == $reform->id}selected{/if}>
                    {$reform->name}
                  </option>
                {/foreach}
              </select>
            </div>
          </div>

          <div class="col-md-6">
            {include "bs/field.tpl" label='notă' name='remark' value=$src->remark}

            {include "bs/field.tpl"
              label='legătura către formatul scanat'
              name='link'
              value=$src->link}

            {capture "help"}
              Trebuie să fie o valoare <code>skey</code> din tabela AdsLink, de exemplu „logos”
              pentru DCR.
            {/capture}
            {include "bs/field.tpl"
              help=$smarty.capture.help
              label='legătura către editură/autor'
              name='courtesyLink'
              value=$src->courtesyLink}

            {include "bs/field.tpl"
              label='textul pentru legătura către editură/autor'
              name='courtesyText'
              value=$src->courtesyText}

            {capture "help"}
            din care digitizate: {$src->ourDefCount};
            procent de completare: {$src->percentComplete|nf:2}.
            {/capture}
            {include "bs/field.tpl"
              help=$smarty.capture.help
              label='număr de definiții (-1 pentru „necunoscut”)'
              name='defCount'
              type='number'
              value=$src->defCount}

            <div class="mb-2">
              <label class="form-label">etichete</label>
              <select name="tagIds[]" class="form-select select2Tags" multiple>
                {foreach $tagIds as $tagId}
                  <option value="{$tagId}" selected></option>
                {/foreach}
              </select>
            </div>

            {capture "help"}
              Glife care permit salvarea definiției fără avertismente. Sînt
              deja incluse glifele <code>{Source::getBaseGlyphsDisplay()|escape}</code>,
              comune tuturor surselor.
            {/capture}
            {include "bs/field.tpl"
              help=$smarty.capture.help
              label='glife comune'
              name='commonGlyphs'
              value=$src->commonGlyphs}

            {include "bs/checkbox.tpl"
              name=normative
              label='sursă normativă'
              checked=$src->normative}

            {include "bs/checkbox.tpl"
              name=hidden
              label='sursă ascunsă, vizibilă doar administratorilor'
              checked=$src->hidden}

            {include "bs/checkbox.tpl"
              name=canModerate
              label='moderatorii pot muta definiții în/din această sursă'
              checked=$src->canModerate}

            {include "bs/checkbox.tpl"
              name=canDistribute
              label='poate fi redistribuită'
              checked=$src->canDistribute}

            {include "bs/checkbox.tpl"
              name=structurable
              label='de structurat în primă fază'
              checked=$src->structurable}

            {include "bs/checkbox.tpl"
              name=hasPageImages
              label='are imagini pentru fiecare pagină'
              checked=$src->hasPageImages}

          </div>
        </div>

        <fieldset>
          <legend>autori (opțional)</legend>

          <table class="table table-sm">
            <thead
              id="authorHeader"
              {if empty($authors)}hidden{/if}>
              <tr>
                <th></th>
                <th>titlu</th>
                <th>nume</th>
                <th>grad</th>
                <th>rol</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="authorContainer" class="sortable" handle=".drag-indicator">
              {include "bits/sourceAuthorEditRow.tpl" id="stem"}
              {foreach $authors as $author}
                {include "bits/sourceAuthorEditRow.tpl"}
              {/foreach}
            </tbody>
          </table>
        </fieldset>

        <button id="addButton" class="btn btn-outline-secondary" type="button">
          {include "bits/icon.tpl" i=add}
          adaugă un autor
        </button>

        <button class="btn btn-primary" type="submit" name="saveButton">
          {include "bits/icon.tpl" i=save}
          <u>s</u>alvează
        </button>

        {if $src->id}
          <a
            class="btn btn-link"
            href="{Router::link('source/view')}/{$src->urlName}">
            renunță
          </a>
        {else}
          <a
            class="btn btn-link"
            href="{Router::link('source/list')}">
            renunță
        {/if}
      </form>
    </div>
  </div>
{/block}
