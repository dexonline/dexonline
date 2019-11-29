<div class="modal-content">

  <div class="modal-header text-center">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
    {include "bits/pageModalActions.tpl" id="sourceDropdownU"}
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
      {include "bits/pageModalActions.tpl" id="sourceDropdownD"}
    </div>
  </div>

</div>
