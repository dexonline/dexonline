{if isset($recentLinks)}
  <div class="modal fade" id="modal-recent" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Pagini vizitate recent</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          {foreach $recentLinks as $rl}
            <div>
              <a href="{$rl->url|escape}">{$rl->text|escape}</a>
            </div>
          {/foreach}

        </div>
      </div>
    </div>
  </div>
{/if}
