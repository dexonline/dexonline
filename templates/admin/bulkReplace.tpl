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
          <input type="checkbox" id="toggleAll" checked>
        </div>
      </div>

      <div class="panel-body" id="panel-body">
        {foreach $objects as $row}
          {if $target == 1}
            {$objId=$row->definition->id}
          {else}
            {$objId=$row->id}
          {/if}

          <div class="checkbox-inline">
            <input type="checkbox"
              class="objCheckbox"
              value="{$objId}"
              checked>
          </div>

          {if $target == 1}
            {include "bits/definition.tpl" showStatus=1 showFlagTypo=1 showUser=0}
          {else}
            {include "bits/meaning.tpl" m=$row}
          {/if}
        {/foreach}
      </div>

      <div class="panel-footer">
        <i class="glyphicon glyphicon-filter"></i>
        <label class="radio-inline">
          <input type="radio" name="radiodiff" checked>Toate diferențele
        </label>
        <label class="radio-inline">
          <input type="radio" name="radiodiff" value="del">Doar ștergerile
        </label>
        <label class="radio-inline">
          <input type="radio" name="radiodiff" value="ins">Doar inserările
        </label>
        <div class="pull-right">
          <span id="chng">{$objects|count}</span>
          <span id="de">{$de}</span>
          {$targetName} vor fi modificate din {$remaining} rămase
        </div>
      </div>
    </div>
  </div>

  <form method="post">
    <input type="hidden" name="search" value="{$search|escape}">
    <input type="hidden" name="replace" value="{$replace|escape}">
    <input type="hidden" name="target" value="{$target}">
    <input type="hidden" name="sourceId" value="{$sourceId}">
    <input type="hidden" name="lastId" value="{$lastId}">
    <input type="hidden" name="limit" value="{$limit}">
    <input type="hidden" name="excludedIds" value="">
    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează {if $objects|count != $remaining} și încarcă următoarele {/if}
    </button>
    <a href="index.php" class="btn btn-primary">
      <i class="glyphicon glyphicon-step-backward"></i>
      înapoi la pagina moderatorului
    </a>
  </form>
{/block}
