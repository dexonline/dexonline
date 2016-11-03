{extends "layout-admin.tpl"}

{block "title"}Înlocuire în masă{/block}

{block "content"}
  <h3>Înlocuire în masă: {$searchResults|count} de definiții</h3>

  <div class="panel-admin">
    {foreach $searchResults as $row}
      {include "bits/definition.tpl" showDropup=0 showStatus=1 showUser=0}
    {/foreach}
  </div>

  <form>
    <input type="hidden" name="search" value="{$search|escape}"/>
    <input type="hidden" name="replace" value="{$replace|escape}"/>
    <input type="hidden" name="sourceId" value="{$sourceId}"/>
    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>
  </form>
{/block}
