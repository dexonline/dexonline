{extends file="admin/layout.tpl"}

{block name=title}Definiții cu greșeli de tipar{/block}

{block name=headerTitle}
  Definiții cu greșeli de tipar ({$searchResults|count})
{/block}

{block name=headerSources}
  <div class="title">
    <form name="frm" class="searchForm" action="#">
      {include file="sourceDropDown.tpl" urlName=1 autosubmit=1}
    </form>
  </div>
{/block}

{block name=content}
  {include file="admin/definitionList.tpl"}
{/block}
