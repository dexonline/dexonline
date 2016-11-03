{extends "layout-admin.tpl"}

{block "title"}Cuvinte{/block}

{block "content"}
  <div id="searchCuv">
    {assign var="text" value=$text|default:false}
    <script> 
     var sel_sources={$s};
     $(document).ready(function(){
       for (var i=0; i<sel_sources.length; i++) {
         var item = document.getElementById('s_' + sel_sources[i]);
         $(item).attr("checked", true);
       }
     });
    </script>
    <form action="{$wwwRoot}cuvinte.php" method="POST" name="frm" onsubmit="return searchSubmit()" id="searchFormCuv">
      
      <div>
        <label for="i">De la: </label><input type="text" name="i" id="i" class="searchFieldCuv" value="{$i|escape}"  maxlength="10" title="De la"/>
        <label for="e"> la: </label><input type="text" name="e" id="e" class="searchFieldCuv" value="{$e|escape}"  maxlength="10" title="la"/>
      </div>
      
      <div class="sourceCheckboxGroup">
        {include "bits/sourceCheckboxGroup.tpl"}
      </div>
      
      <div>
        <input type="submit" value="caută" id="searchButton"/>
      </div>
      
    </form>
    <div class="clearer"></div> 
  </div>

  {assign var="results" value=$results|default:null}
  {foreach $results as $row}
    {include "bits/definition.tpl"}
  {/foreach}
{/block}
