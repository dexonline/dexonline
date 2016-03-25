<div class="container">
  {foreach from=$flashMessages item=m}
    <div class="flashMessage flashMessage-{$m.type}">
      {$m.text}
    </div>
  {/foreach}
</div>
