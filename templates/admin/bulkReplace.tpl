{extends "layout-admin.tpl"}

{block "title"}Înlocuire în masă{/block}

{block "content"}
  <h3>Înlocuire în masă</h3>

  <div class="panel-admin">
    <div class="panel panel-default">
      <div class="panel-heading" id="panel-heading">
        <i class="glyphicon glyphicon-user"></i>
        {$modUser}
        <div class="checkbox-inline">
          <input type="checkbox" name="checkbox-all" id="defall" checked />
        </div>
      </div>
      <div class="panel-body" id="panel-body">
        {foreach $searchResults as $row}
          <div class="checkbox-inline">
            <input type="checkbox" name="checkbox-def" id="def{$row->definition->id}" value="{$row->definition->id}" checked />
          </div>
          {include "bits/definition.tpl" showStatus=1 showFlagTypo=1 showUser=0}
        {/foreach}
      </div>
      <div class="panel-footer">
        <i class="glyphicon glyphicon-filter"></i>
        <label class="radio-inline">
          <input type="radio" name="radiodiff" checked >Toate diferențele
        </label>
        <label class="radio-inline">
          <input type="radio" name="radiodiff" value="onlyDeletions" >Doar ștergerile
        </label>
        <label class="radio-inline">
          <input type="radio" name="radiodiff" value="onlyInsertions" >Doar inserările
        </label>
        <div class="pull-right">
          <span id="chng">{$searchResults|count}</span><span id="de">{$de}</span> definiții vor fi modificate din {$remainedDefs} rămase
        </div>
      </div>
    </div>
  </div>

  <form method="post">
    <input type="hidden" name="search" value="{$search|escape}">
    <input type="hidden" name="replace" value="{$replace|escape}">
    <input type="hidden" name="sourceId" value="{$sourceId}">
    <input type="hidden" name="lastId" value="{$lastId}">
    <input type="hidden" name="maxaffected" value="{$maxaffected}">
    <input type="hidden" name="excludedIds" value="">
    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează {if $searchResults|count != $remainedDefs} și încarcă următoarele {/if}
    </button>
    <button type="button" class="btn btn-primary" name="backButton">
      <i class="glyphicon glyphicon-step-backward"></i>
      înapoi la pagina moderatorului
    </button>
  </form>
{/block}
