{extends file="admin/layout.tpl"}

{block name=title}Clonare model{/block}

{block name=headerTitle}
  Clonare model {$modelType}{$modelNumber}
{/block}

{block name=content}
  <form action="cloneModel.php" method="post" onsubmit="this.bogusButton.disabled = true;">
    <input type="hidden" name="modelType" value="{$modelType}"/>
    <input type="hidden" name="modelNumber" value="{$modelNumber}"/>

    Număr nou de model:
    <input type="text" name="newModelNumber" value="{$newModelNumber|escape}"/>
    <br/>

    <input type="checkbox" id="chooseLexems" name="chooseLexems"
           value="1" checked="checked"/>
    <label for="chooseLexems">Doresc să migrez acum lexemele</label>
    <br/>

    <div id="lexemDiv" class="cm_lexemDiv" style="display: block">
      Bifați lexemele pe care doriți să le migrați la noul model:
      <br/>

      {foreach $lexems as $l}
        <label>
          <input type="checkbox" name="lexemId[]" value="{$l->id}">
          {include file="bits/lexemName.tpl" lexem=$l}
          <span class="deemph">({$l->modelType}{$l->modelNumber})</span>
        </label>
        <br>
      {/foreach}
    </div>

    <!-- We want to disable the button on click, but still submit a value -->
    <input type="hidden" name="cloneButton" value="1"/>
    <input type="submit" name="bogusButton" value="Clonează"/>
  </form>
{/block}
