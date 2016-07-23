<div class="col-md-7 col-md-offset-3">
  {foreach from=$flashMessages item=m}
    <div class="alert alert-{$m.type} alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      {$m.text}
    </div>
  {/foreach}
</div>
