{extends file="layout.tpl"}

{block name=title}
  {if $src->id}
    Editare sursă {$src->shortName}
  {else}
    Adăugare sursă
  {/if}
{/block}

{block name=banner}{/block}
{block name=search}{/block}

{block name=content}

  <div class="panel panel-default">
    <div class="panel-heading">
      {if $src->name}Editare sursă: {$src->name}{else}Adăugare sursă{/if}
      <a class="btn btn-xs btn-default pull-right" href="surse">înapoi la lista de surse</a>
    </div>

    <div class="panel-body">

      <form method="post" action="editare-sursa">
        <input type="hidden" name="id" value="{$src->id}" />

        <div class="form-group">
          <label>Nume</label>
          <input type="text" name="name" value="{$src->name}" class="form-control" />
        </div>

        <div class="form-group">
          <label>Nume scurt</label>
          <input type="text" name="shortName" value="{$src->shortName}" class="form-control" />
          <p class="help-block">
            Numele sursei prezentat după fiecare definiție.
          </p>
        </div>

        <div class="form-group">
          <label>Nume URL</label>
          <input type="text" name="urlName" value="{$src->urlName}" class="form-control" />
          <p class="help-block">
            Numele care apare în URL la căutarea într-o anumită sursă, cum ar fi
            https://dexonline.ro/definitie-<strong>der</strong>/copil
          </p>
        </div>

        <div class="form-group">
          <label>Autor</label>
          <input type="text" name="author" value="{$src->author}" class="form-control" />
        </div>

        <div class="form-group">
          <label>Editură</label>
          <input type="text" name="publisher" value="{$src->publisher}" class="form-control" />
        </div>

        <div class="form-group">
          <label>An</label>
          <input type="text" name="year" value="{$src->year}" class="form-control" />
        </div>

        <div class="form-group">
          <label>Legătura către formatul scanat</label>
          <input type="text" name="link" value="{$src->link}" class="form-control" />
        </div>

        <div class="form-group">
          <label>Tip:</label>
          <select class="form-control" name="isOfficial">
            <option value="3" {if $src->isOfficial==3 }selected{/if}>Ascuns</option>
            <option value="2" {if $src->isOfficial==2 }selected{/if}>Oficial</option>
            <option value="1" {if $src->isOfficial==1 }selected{/if}>Specializat</option>
            <option value="0" {if $src->isOfficial==0 }selected{/if}>Neoficial</option>
          </select>
        </div>

        <div class="form-group">
          <label>Număr de definiții (-1 pentru „necunoscut”)</label>
          <input type="text" name="defCount" value="{$src->defCount}" class="form-control" />
          <p class="help-block">
            din care digitizate: {$src->ourDefCount}; procent de completare: {$src->percentComplete|string_format:"%.2f"}.
          </p>
        </div>

        <div class="checkbox">
          <label for="cbIsActive">
            <input type="checkbox" id="cbIsActive" name="isActive" value="1" {if $src->isActive}checked="checked"{/if} />
            Sursa este activă (și vizibilă tuturor utilizatorilor)
          </label>
        </div>

        <div class="checkbox">
          <label for="cbCanContribute">
            <input type="checkbox" id="cbCanContribute" name="canContribute" value="1" {if $src->canContribute}checked="checked"{/if} />
            Deschisă pentru contribuții
          </label>
        </div>

        <div class="checkbox">
          <label for="cbCanModerate">
            <input type="checkbox" id="cbCanModerate" name="canModerate" value="1" {if $src->canModerate}checked="checked"{/if} />
            Poate fi aleasă de moderatori
          </label>
        </div>

        <div class="checkbox">
          <label for="cbCanDistribute">
            <input type="checkbox" id="cbCanDistribute" name="canDistribute" value="1" {if $src->canDistribute}checked="checked"{/if} />
            Poate fi redistribuită
          </label>
        </div>

        <button class="btn btn-primary" type="submit" name="saveButton">
          <i class="glyphicon glyphicon-floppy-disk"></i>
          salvează
        </button>
        <a class="btn btn-link" href="">renunță</a>
      </form>
    </div>
{/block}
