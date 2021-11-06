{if !isset($PAGE_MODAL_ONCE)}
  {$PAGE_MODAL_ONCE=1 scope="global"}
  <script>
    const URL_PATTERN = '{Config::STATIC_URL}' + '{Config::PAGE_URL_PATTERN}';
  </script>

  <div id="pageModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          {include "bits/pageModalActions.tpl"}
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body text-center">
          <div class="pageScan">
            {* loading spinner *}
            <div id="loading" class="spinner-border text-primary" role="status">
              <span class="visually-hidden">încarc pagina cerută...</span>
            </div>
            <img id="pageImage">
            {notice icon="error"}{/notice}
          </div>
        </div>

        <div class="modal-footer">
          {include "bits/pageModalActions.tpl"}
        </div>

      </div>
    </div>
  </div>

{/if}
