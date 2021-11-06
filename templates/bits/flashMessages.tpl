{if $flashMessages}
  <div class="w-75 mx-auto mt-3">
    {foreach $flashMessages as $m}
      {notice type=$m.type}
        {$m.text}
      {/notice}
    {/foreach}
  </div>
{/if}
