{if $flashMessages}
  <div class="mt-3">
    {foreach $flashMessages as $m}
      {notice type=$m.type}
        {$m.text}
      {/notice}
    {/foreach}
  </div>
{/if}
