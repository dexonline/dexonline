{extends "layout-admin.tpl"}

{block "title"}Definiții cu glife rare, fără etichetă{/block}

{block "content"}
  <h3>{$searchResults|count} definiții cu glife rare, fără etichetă</h3>

  <form method="post">
    <div class="row row-cols-sm-auto g-2 align-items-center">
      <div class="col-12">
        <label class="col-form-label">sursa</label>
      </div>
      <div class="col-12 w-50">
        {include "bits/sourceDropDown.tpl" urlName=1 autosubmit=1}
      </div>
    </div>

    {foreach $searchResults as $row}
      {include "bits/definition.tpl" showSelectCheckbox=1 showPageLink=0}
    {/foreach}

    {if count($searchResults)}
      <div>
        <button type="submit" class="btn btn-primary">
          {include "bits/icon.tpl" i=add}
          adaugă eticheta [{$tag->value}]
        </button>
      </div>
    {/if}

  </form>

{/block}
