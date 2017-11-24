{if !isset($PAGE_MODAL_ONCE)}
  {$PAGE_MODAL_ONCE=1 scope="global"}

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
          <img id="pageImage">
          <div class="alert alert-danger" role="alert"></div>
        </div>

        <div class="modal-footer">
          <div class="text-center">
            {include "bits/pageModalActions.tpl"}
          </div>
        </div>

      </div>
    </div>
  </div>

  <div id="pageModalSpinner">
    <img src="{$imgRoot}/spinning-circles.svg">
  </div>

{/if}
