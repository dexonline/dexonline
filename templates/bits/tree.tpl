{$link=$link|default:false}
{$class=$class|default:''}
{if $link}
  <a href="{Router::link('tree/edit')}?id={$t->id}" class="{$class}" title="editează">
    {$t->description}
  </a>
{else}
  {$t->description}
{/if}
