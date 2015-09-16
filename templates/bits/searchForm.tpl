{assign var="advancedSearch" value=$advancedSearch|default:false}
{assign var="cuv" value=$cuv|default:''}
{assign var="text" value=$text|default:false}



<form action="{$wwwRoot}search.php" name="frm" onsubmit="return searchSubmit()" id="searchForm">

  <div class="searchTextField">
    <input type="text" name="cuv" class="searchField" value="{$cuv|escape}" maxlength="50"/>
    <input type="submit" value="caută" id="searchButton" class="btn"/>
  </div>

  {if !$advancedSearch}
    <a href="#" onclick="return toggleDivVisibility('advSearch')" id="advancedAnchor">căutare avansată</a>
  {/if}

  <div id="advSearch" {if !$advancedSearch}style="display: none"{/if}>
    <input type="checkbox" name="text" value="1" id="defBody" {if $text}checked="checked"{/if}/>
    <label for="defBody">Caută în tot textul definițiilor</label>
    {include file="sourceDropDown.tpl" urlName=1}
    <a id="advSearchHelp" href="http://wiki.dexonline.ro/wiki/Ajutor_pentru_căutare" target="_blank">ajutor</a>
  </div>
</form>
<div class="clearer"></div>
<script>
  {if $cfg.search.acEnable}
    searchInit(true, {$cfg.search.acMinChars});
  {else}
    searchInit(false);
  {/if}
</script>
