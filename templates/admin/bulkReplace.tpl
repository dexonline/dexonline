{extends "layout-admin.tpl"}

{block "title"}Înlocuire în masă{/block}

{block "content"}
  <h3>Înlocuire în masă: {$searchResults|count} de definiții</h3>

  <div class="panel-admin">
      <div class="panel panel-default">
        <div class="panel-heading">
          <i class="glyphicon glyphicon-user"></i>
            {$modUser}
        </div>

      {foreach $diffs as $d}
      {if isset($d)}
        <div class="panel-body">
          <p>{$d}</p>
        </div>
      {/if}
      {/foreach}

      </div>
  </div>

  <form>
    <input type="hidden" name="search" value="{$search|escape}">
    <input type="hidden" name="replace" value="{$replace|escape}">
    <input type="hidden" name="sourceId" value="{$sourceId}">
    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>
  </form>
{/block}
