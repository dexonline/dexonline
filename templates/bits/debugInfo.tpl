<div>
  Pagină generată în {$debug_runningTimeMillis} ms.<br/>
  {foreach from=$debug_messages item=line}
    {$line|escape}<br/>
  {/foreach}
  {foreach from=$debug_ormQueryLog item=query}
    Idiorm query: {$query}<br/>
  {/foreach}
</div>
