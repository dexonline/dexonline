{extends "layout-admin.tpl"}

{block "title"}Definiții cu glife rare, fără etichetă{/block}

{block "content"}
  <h3>{$searchResults|count} definiții cu glife rare, fără etichetă</h3>

  <form method="post" class="form-horizontal">
    <div class="form-group">
      <label class="col-md-1 control-label">sursa</label>
      <div class="col-md-11">
        {include "bits/sourceDropDown.tpl" urlName=1 autosubmit=1}
      </div>
    </div>

    <div class="voffset3"></div>

    {foreach $searchResults as $row}
      {include "bits/definition.tpl" showSelectCheckbox=1 showPageLink=0}
    {/foreach}

    {if count($searchResults)}
      <div>
        <button type="submit" class="btn btn-primary">
          <i class="glyphicon glyphicon-plus"></i>
          adaugă eticheta [{$tag->value}]
        </button>
      </div>
    {/if}

  </form>

{/block}
