{if isset($recentLinks)}
  <div class="adminRecentlyVisited">
    <div class="title">Pagini recent vizitate</div>
  
    {foreach $recentLinks as $rl}
      <a href="{$rl->url|escape}">{$rl->text|escape}</a><br/>
    {/foreach}
  </div>
{/if}
