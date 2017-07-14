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
          </div>
        </div>
      </div>

      <div class="col-md-6">

        <div class="form-group"">
          <label for="color" class="col-md-2 control-label">
            culoare
          </label>
          <div class="col-md-10">
            <div>
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
        </div>

        <div class="form-group"">
          <label for="background" class="col-md-2 control-label">
            fundal
          </label>
          <div class="col-md-10">
            <div>
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
        </div>

      </div>
    </div>

    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>

    <a class="btn btn-default" href="etichete">
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

  {if count($lexems)}
    <h3>
      Lexeme asociate
      {if $lexemCount > count($lexems)}
        ({count($lexems)} din {$lexemCount} afișate)
      {else}
        ({count($lexems)})
      {/if}
    </h3>

    {include "bits/lexemList.tpl"}
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
              <a href="editTree.php?id={$m->getTree()->id}">
                {$m->getTree()->description}
              </a>
            </td>
            <td>
              <strong>{$m->breadcrumb}</strong>
              {$m->htmlRep}
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
