{extends "layout.tpl"}

{block "title"}Topul voluntarilor{/block}

{block "content"}
  <h3>{'Manual contribution rankings'|_}</h3>

  <p>
    {'For every user the number of definitions and the total character length
    of those definitions are shown. For comparison, the Bible has roughly
    3,500,000 characters.'|_}
  </p>

  {include "bits/top.tpl" data=$manualData tableId="manualTop" pager=1}

  <h3>{'Automated contribution rankings'|_}</h3>

  <p>
    {'These include definitions entered using a program.'|_}
  </p>

  {include "bits/top.tpl" data=$bulkData tableId="bulkTop" pager=0}
{/block}
