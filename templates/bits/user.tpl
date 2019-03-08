{if $u}
  <a href="{Router::link('user/view')}/{$u->nick}">
    {$u->nick}
  </a>
{else}
  anonim
{/if}
