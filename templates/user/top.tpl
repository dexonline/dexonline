{extends "layout.tpl"}

{block "title"}Topul voluntarilor{/block}

{block "content"}
  <h3>{t}Manual contribution rankings{/t}</h3>

  <p>
    {t}For every user the number of definitions and the total character length
    of those definitions are shown. For comparison, the Bible has roughly
    3,500,000 characters.{/t}
  </p>

  <h4>{t}Last year ranking{/t}</h4>

  {include "bits/top.tpl" data=$lastyearData tableId="lastyearTop" pager=0}

  <h4>{t}Global ranking{/t}</h4>

  {include "bits/top.tpl" data=$manualData tableId="manualTop" pager=1}

  <h3>{t}Automated contribution rankings{/t}</h3>

  <p>
    {t}These include definitions entered using a program.{/t}
  </p>

  {include "bits/top.tpl" data=$bulkData tableId="bulkTop" pager=0}
{/block}
