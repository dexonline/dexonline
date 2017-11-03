{if !isset($PAGE_MODAL_ONCE)}
  {$PAGE_MODAL_ONCE=1 scope="global"}

  <div id="pageModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>

          <h4 class="modal-title">
            <a id="prevPage" class="btn btn-link" title="pagina anterioară">
              <i class="glyphicon glyphicon-chevron-left"></i>
            </a>
            Pagina originală
            <a id="nextPage" class="btn btn-link" title="pagina următoare">
              <i class="glyphicon glyphicon-chevron-right"></i>
            </a>
          </h4>

        </div>

        <div class="modal-body text-center">
          <img id="pageImage">
          <div class="alert alert-danger" role="alert"></div>
        </div>

      </div>
    </div>
  </div>

  <div id="pageModalSpinner">
    <img src="{$imgRoot}/spinning-circles.svg">
  </div>

{/if}
