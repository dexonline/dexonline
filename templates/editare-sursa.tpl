{extends "layout-admin.tpl"}

{block "title"}
  {if $src->id}
    Editare sursă {$src->shortName}
  {else}
    Adăugare sursă
  {/if}
{/block}

{block "content"}

  <div class="panel panel-default">
    <div class="panel-heading">
      {if $src->name}Editare sursă: {$src->name}{else}Adăugare sursă{/if}
      <a class="btn btn-xs btn-default pull-right" href="surse">înapoi la lista de surse</a>
    </div>

    <div class="panel-body">

      <form method="post" action="editare-sursa">
        <input type="hidden" name="id" value="{$src->id}">

        <div class="row">

          <div class="col-md-6">

            <div class="form-group">
              <label>nume</label>
              <input type="text" name="name" value="{$src->name}" class="form-control">
            </div>

            <div class="form-group">
              <label>nume scurt</label>
              <input type="text" name="shortName" value="{$src->shortName}" class="form-control">
              <p class="help-block">
                Numele sursei prezentat după fiecare definiție.
              </p>
            </div>

            <div class="form-group">
              <label>nume URL</label>
              <input type="text" name="urlName" value="{$src->urlName}" class="form-control">
              <p class="help-block">
                Numele care apare în URL la căutarea într-o anumită sursă, cum ar fi
                https://dexonline.ro/definitie-<strong>der</strong>/copil
              </p>
            </div>

            <div class="form-group">
              <label>autor</label>
              <input type="text" name="author" value="{$src->author}" class="form-control">
            </div>

            <div class="form-group">
              <label>editură</label>
              <input type="text" name="publisher" value="{$src->publisher}" class="form-control">
            </div>

            <div class="form-group">
              <label>an</label>
              <input type="text" name="year" value="{$src->year}" class="form-control">
            </div>

            <div class="form-group">
              <label>tipul sursei</label>
              <select class="form-control" name="sourceTypeId">
                <option>Fără categorie</option>
                {foreach $sourceTypes as $type}
                  <option value="{$type->id}" {if $src->sourceTypeId == $type->id}selected{/if}>
                    {$type->name}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="form-group">
              <label>managerul dicționarului</label>
              <select class="form-control" name="managerId">
                <option>Fără moderator</option>
                {foreach $managers as $manager}
                  <option value="{$manager->id}" {if $src->managerId == $manager->id}selected{/if}>
                    {$manager->name}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="form-group">
              <label>tipul importului</label>
              <select class="form-control" name="importType">
                {foreach Source::$IMPORT_TYPE_LABELS as $importType => $label}
                  <option value="{$importType}" {if $src->importType == $importType}selected{/if}>
                    {$label}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="form-group">
              <label>reforma ortografică</label>
              <select class="form-control" name="reformId">
                <option>Fără categorie</option>
                {foreach $reforms as $reform}
                  <option value="{$reform->id}" {if $src->reformId == $reform->id}selected{/if}>
                    {$reform->name}
                  </option>
                {/foreach}
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>notă</label>
              <input type="text" name="remark" value="{$src->remark}" class="form-control">
            </div>

            <div class="form-group">
              <label>legătura către formatul scanat</label>
              <input type="text" name="link" value="{$src->link}" class="form-control">
            </div>

            <div class="form-group">
              <label>legătura către editură/autor</label>
              <input type="text" name="courtesyLink" value="{$src->courtesyLink}" class="form-control">
              <p class="help-block">
                Trebuie să fie o valoare <code>skey</code> din tabela AdsLink, de exemplu „logos”
                pentru DCR.
              </p>
            </div>

            <div class="form-group">
              <label>textul pentru legătura către editură/autor</label>
              <input type="text" name="courtesyText" value="{$src->courtesyText}" class="form-control">
            </div>

            <div class="form-group">
              <label>tip</label>
              <select class="form-control" name="type">
                {foreach Source::$TYPE_NAMES as $type => $name}
                  <option value="{$type}" {if $src->type == $type}selected{/if}>
                    {$name}
                  </option>
                {/foreach}
              </select>
            </div>

            <div class="form-group">
              <label>număr de definiții (-1 pentru „necunoscut”)</label>
              <input type="text" name="defCount" value="{$src->defCount}" class="form-control">
              <p class="help-block">
                din care digitizate: {$src->ourDefCount}; procent de completare: {$src->percentComplete|string_format:"%.2f"}.
              </p>
            </div>

            <div class="form-group">
              <label>etichete</label>
              <select name="tagIds[]" class="form-control select2Tags" multiple>
                {foreach $tagIds as $tagId}
                  <option value="{$tagId}" selected></option>
                {/foreach}
              </select>
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="isActive" {if $src->isActive}checked{/if}>
                sursă activă și vizibilă tuturor utilizatorilor
              </label>
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="canContribute" {if $src->canContribute}checked{/if}>
                deschisă pentru contribuții
              </label>
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="canModerate" {if $src->canModerate}checked{/if}>
                poate fi aleasă de moderatori
              </label>
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="canDistribute" {if $src->canDistribute}checked{/if}>
                poate fi redistribuită
              </label>
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="structurable" {if $src->structurable}checked{/if}>
                de structurat în primă fază
              </label>
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="hasPagePdfs" {if $src->hasPagePdfs}checked{/if}>
                are PDF-uri pentru fiecare pagină
              </label>
            </div>

          </div>
        </div>

        <button class="btn btn-success" type="submit" name="saveButton">
          <i class="glyphicon glyphicon-floppy-disk"></i>
          <u>s</u>alvează
        </button>
        <a class="btn btn-link" href="">renunță</a>
      </form>
    </div>
  </div>
{/block}
