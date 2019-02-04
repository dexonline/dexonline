{if $u}
  <a href="{Config::URL_PREFIX}utilizator/{$u->nick}">
    {$u->nick}
  </a>
{else}
  anonim
{/if}
