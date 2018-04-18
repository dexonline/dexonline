{extends "layout-admin.tpl"}

{block "title"}Înlocuire în masă{/block}

{block "content"}
  <h3>Înlocuire în masă</h3>

  <div class="panel-admin">
    <div class="panel panel-default">
      <div class="panel-heading clearfix" id="panel-heading">
        {if $target == 1}{* display the toggleAll checkboxes only for definitions *}
          <div class="btn-group pull-right checkbox-hidden">
            <label id="labelStructured" class="btn btn-sm btn-primary">
              <input id="toggleAllStructured" class="toggleAll structured" type="checkbox" checked>
              <i class="glyphicon glyphicon-ok"></i>
              Structurate
            </label>
            <label id="labelUnstructured" class="btn btn-sm btn-default">
              <input id="toggleAllUnstructured" class="toggleAll unstructured" type="checkbox" checked>
              <i class="glyphicon glyphicon-ok"></i>
              Nestructurate
            </label>
          </div>
        {/if}
        <i class="glyphicon glyphicon-user"></i>
        {$modUser}
      </div>

      <div class="panel-body" id="panel-body">
        {foreach $objects as $row}
          {if $target == 1}
            {$objId=$row->definition->id}
          {else}
            {$objId=$row->id}
          {/if}
          
          {if $target == 1}{* display the checkbox only for definitions *}
            <div class="btn pull-right checkbox-hidden">
              <label class="btn btn-xs{if $row->definition->structured} btn-primary{else} btn-default{/if}">
                <input class="objCheckbox{if $row->definition->structured} structured{else} unstructured{/if}" 
                       type="checkbox" 
                       value="{$objId}" 
                       checked>
                <i class="glyphicon glyphicon-ok"></i>
              </label>
            </div>
          {/if}                     

          {if $target == 1}
            {include "bits/definition.tpl" showStatus=1 showFlagTypo=1 showUser=0 showStructuredWrapper=0}
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
    <input type="hidden" name="limit" value="{$limit}">
    <input type="hidden" name="excludedIds" value="">
    <input type="hidden" name="structuredChanged" value="{$structuredChanged}">
    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează {if $objects|count != $remaining} și încarcă următoarele {/if}
    </button>
    <a href="index.php" class="btn btn-primary">
      <i class="glyphicon glyphicon-step-backward"></i>
      înapoi la pagina moderatorului
    </a>
    {if $structuredChanged}
      <a href="bulkReplaceStructured.php" class="btn btn-primary pull-right" target="_blank">
        <i class="glyphicon glyphicon-list"></i>
        definiții structurate modificate
      </a>
    {/if}
  </form>
{/block}
