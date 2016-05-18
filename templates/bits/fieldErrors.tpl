{foreach from=$errors|default:null item=e}
  <div class="text-danger">{$e}</div>
{/foreach}
