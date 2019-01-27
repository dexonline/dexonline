{extends "layout-admin.tpl"}

{block "title"}Definiții cu eticheta inutilă [{$tag->value}]{/block}

{block "content"}
  <h3>{$searchResults|count} definiții cu eticheta inutilă [{$tag->value}]</h3>

  <form method="post">
    {foreach $searchResults as $row}
      {include "bits/definition.tpl" showSelectCheckbox=1 showPageLink=0}
    {/foreach}

    {if count($searchResults)}
      <div>
        <button type="submit" class="btn btn-primary">
          <i class="glyphicon glyphicon-trash"></i>
          șterge eticheta [{$tag->value}]
        </button>
      </div>
    {/if}
    
  </form>

{/block}
