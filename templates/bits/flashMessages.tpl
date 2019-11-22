{if $flashMessages}
  <div class="row voffset2">
    <div class="col-md-6 col-md-offset-3">
      {foreach $flashMessages as $m}
        <div class="alert alert-{$m.type} alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          {$m.text}
        </div>
      {/foreach}
    </div>
  </div>
{/if}
