{$link=$link|default:false}
{$class=$class|default:''}
{if $link}
  <a href="{Config::URL_PREFIX}editTree.php?id={$t->id}" class="{$class}" title="editează">
    {$t->description}
  </a>
{else}
  {$t->description}
{/if}
