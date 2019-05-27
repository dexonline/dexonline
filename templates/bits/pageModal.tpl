{if !isset($PAGE_MODAL_ONCE)}
  {$PAGE_MODAL_ONCE=1 scope="global"}
  <script>
    const URL_PATTERN = '{Config::STATIC_URL}' + '{Config::PAGE_URL_PATTERN}';
  </script>

  <div id="pageModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header text-center">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>

          {include "bits/pageModalActions.tpl"}
        </div>

        <div class="modal-body text-center">
          <div class="pageScan">
            {include "bits/loading.tpl"}
            <img id="pageImage">
            <div class="alert alert-danger" role="alert"></div>
          </div>
        </div>

        <div class="modal-footer">
          <div class="text-center">
            {include "bits/pageModalActions.tpl"}
          </div>
        </div>

      </div>
    </div>
  </div>

{/if}
