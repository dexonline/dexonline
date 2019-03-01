{extends "layout-admin.tpl"}

{block "title"}Eticheta {$t->value}{/block}

{block "content"}
  {if $t->id}
    <h3>Eticheta {$t->value}</h3>
  {else}
    <h3>Adaugă o etichetă</h3>
  {/if}

  {include "bits/tagAncestors.tpl" tag=$t}

  <form class="form-horizontal voffset3" method="post" role="form">
    <input type="hidden" name="id" value="{$t->id}">

    <div class="row">

      <div class="col-md-6">
        <div class="form-group {if isset($errors.value)}has-error{/if}">
          <label for="value" class="col-md-2 control-label">
            nume
          </label>
          <div class="col-md-10">
            <div>
              <input type="text"
                     class="form-control"
                     id="value"
                     name="value"
                     value="{$t->value}">
              {include "bits/fieldErrors.tpl" errors=$errors.value|default:null}
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="tooltip" class="col-md-2 control-label">
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

        <div class="form-group {if isset($errors.parentId)}has-error{/if}">
          <label for="parentId" class="col-md-2 control-label">
            părinte
          </label>
          <div class="col-md-10">
            <div>
              <select id="parentId" name="parentId" class="form-control">
                {if $t->parentId}
                  <option value="{$t->parentId}" selected></option>
                {/if}
              </select>
              {include "bits/fieldErrors.tpl" errors=$errors.parentId|default:null}
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox"
                  name="public"
                  value="1"
                  {if $t->public}checked{/if}>
                publică
              </label>
            </div>

            <p class="help-block">
              Dacă nu este publică, eticheta este vizibilă doar pentru
              utilizatorii privilegiați.
            </p>

          </div>
        </div>

      </div>

      <div class="col-md-6">

        <div class="form-group"">
          <label for="color" class="col-md-2 control-label">
            culoare
          </label>
          <div class="col-md-10">
            <div class="input-group colorpicker-component">
              <span class="input-group-addon"><i></i></span>
              <input type="text"
                     class="form-control"
                     id="color"
                     name="color"
                     value="{$t->getColor()}">
            </div>
          </div>
        </div>

        <div class="form-group"">
          <label for="background" class="col-md-2 control-label">
            fundal
          </label>
          <div class="col-md-10">
            <div class="input-group colorpicker-component">
              <span class="input-group-addon"><i></i></span>
              <input type="text"
                     class="form-control"
                     id="background"
                     name="background"
                     value="{$t->getBackground()}">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="icon" class="col-md-2 control-label">
            iconiță
          </label>
          <div class="col-md-10">
            <div class="input-group">
              <input type="text"
                     class="form-control"
                     id="icon"
                     name="icon"
                     value="{$t->icon}">
              <span class="input-group-addon">
                {if $t->icon}
                  <i class="glyphicon glyphicon-{$t->icon}"></i>
                {/if}
              </span>
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox"
                       name="iconOnly"
                       value="1"
                       {if $t->iconOnly}checked{/if}>
                arată doar iconița fără text
              </label>
            </div>

            <p class="help-block">
              Opțional, un nume de <a href="http://getbootstrap.com/components/#glyphicons">
              glyphicon</a>. Copiați doar fragmentul de după <em>glyphicon-</em>, de exemplu
              <em>plus</em> sau <em>euro</em>.
            </p>

          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>

    <a class="btn btn-default" href="{Router::link('tag/list')}">
      <i class="glyphicon glyphicon-arrow-left"></i>
      înapoi la lista de etichete
    </a>

    <a class="btn btn-link" href="{if $t->id}?id={$t->id}{/if}">
      renunță
    </a>

    <button type="submit"
            name="deleteButton"
            class="btn btn-danger pull-right"
            {if !$canDelete}
            disabled
            title="Nu puteți șterge eticheta deoarece (1) are descendenți sau (2) este folosită."
            {/if}
            >
      <i class="glyphicon glyphicon-trash"></i>
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
    <h3>
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
    <h3>
      Sensuri asociate
      {if $meaningCount > count($meanings)}
        ({count($meanings)} din {$meaningCount} afișate)
      {else}
        ({count($meanings)})
      {/if}
    </h3>

    <table class="table table-condensed table-bordered">
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

  {* frequent colors to be used by the color pickers *}
  {foreach $frequentColors as $color => $list}
    <div id="frequent-{$color}">
      {foreach $list as $color}
        <div>{$color}</div>
      {/foreach}
    </div>
  {/foreach}
{/block}
