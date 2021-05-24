{if $flashMessages}
  <div class="w-75 mx-auto mt-3">
    {foreach $flashMessages as $m}
      <div class="alert alert-{$m.type} alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
        {$m.text}
      </div>
    {/foreach}
  </div>
{/if}
