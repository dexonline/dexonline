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

            <div class="mb-2">
              <label class="form-label">nume</label>
              <input type="text" name="name" value="{$src->name}" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">nume scurt</label>
              <input type="text" name="shortName" value="{$src->shortName}" class="form-control">
              <p class="form-text">
                Numele sursei prezentat după fiecare definiție.
              </p>
            </div>

            <div class="mb-2">
              <label class="form-label">nume URL</label>
              <input type="text" name="urlName" value="{$src->urlName}" class="form-control">
              <p class="form-text">
                Numele care apare în URL la căutarea într-o anumită sursă, cum ar fi
                https://dexonline.ro/definitie-<strong>der</strong>/copil
              </p>
            </div>

            <div class="mb-2">
              <label class="form-label">autor</label>
              <input type="text" name="author" value="{$src->author}" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">editură</label>
              <input type="text" name="publisher" value="{$src->publisher}" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">an</label>
              <input type="text" name="year" value="{$src->year}" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">tipul sursei</label>
              <select class="form-select" name="sourceTypeId">
                <option value="0">Fără categorie</option>
                {foreach $sourceTypes as $type}
                  <option value="{$type->id}" {if $src->sourceTypeId == $type->id}selected{/if}>
                    {$type->name}
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
            <div class="mb-2">
              <label class="form-label">notă</label>
              <input type="text" name="remark" value="{$src->remark}" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">legătura către formatul scanat</label>
              <input type="text" name="link" value="{$src->link}" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">legătura către editură/autor</label>
              <input type="text" name="courtesyLink" value="{$src->courtesyLink}" class="form-control">
              <p class="form-text">
                Trebuie să fie o valoare <code>skey</code> din tabela AdsLink, de exemplu „logos”
                pentru DCR.
              </p>
            </div>

            <div class="mb-2">
              <label class="form-label">textul pentru legătura către editură/autor</label>
              <input type="text" name="courtesyText" value="{$src->courtesyText}" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">tip</label>
              <select class="form-select" name="type">
                {foreach Source::TYPE_NAMES as $type => $name}
                  <option value="{$type}" {if $src->type == $type}selected{/if}>
                    {$name}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">număr de definiții (-1 pentru „necunoscut”)</label>
              <input type="text" name="defCount" value="{$src->defCount}" class="form-control">
              <p class="form-text">
                din care digitizate: {$src->ourDefCount};
                procent de completare: {$src->percentComplete|nf:2}.
              </p>
            </div>

            <div class="mb-2">
              <label class="form-label">etichete</label>
              <select name="tagIds[]" class="form-select select2Tags" multiple>
                {foreach $tagIds as $tagId}
                  <option value="{$tagId}" selected></option>
                {/foreach}
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">glife comune</label>
              <input
                type="text"
                name="commonGlyphs"
                value="{$src->commonGlyphs|escape}"
                class="form-control">

              <p class="form-text">
                Glife care permit salvarea definiției fără avertismente. Sînt
                deja incluse glifele <code>{Source::getBaseGlyphsDisplay()|escape}</code>,
                comune tuturor surselor.
              </p>
            </div>

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
            <tbody id="authorContainer">
              {include "bits/sourceAuthorEditRow.tpl" id="stem"}
              {foreach $authors as $author}
                {include "bits/sourceAuthorEditRow.tpl"}
              {/foreach}
            </tbody>
          </table>
        </fieldset>

        <button id="addButton" class="btn btn-light" type="button">
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
