{if isset($recentLinks)}
  <div class="modal fade" id="recentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Pagini vizitate recent</h4>
        </div>
        <div class="modal-body">

          {foreach $recentLinks as $rl}
            <a href="{$rl->url|escape}">{$rl->text|escape}</a><br>
          {/foreach}

        </div>
      </div>
    </div>
  </div>
{/if}
