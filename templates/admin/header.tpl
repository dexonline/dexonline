<div class="header">
  <div class="title">
    {$title|escape}
    {if $count !== null}({$count}){/if}
  </div>

  {if $showSources}
    <div class="title"> 
      <form name="frm" class="searchForm" action="#">
        {include file="sourceDropDown.tpl" urlName=1 autosubmit=1}
      </form>
    </div>
  {/if}

  <div class="links">
    <a href="../">dexonline</a> |
    <a href="../admin/">Pagina moderatorului</a>
  </div>
  <div style="clear: both;"></div>
</div>
