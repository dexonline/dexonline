{* Include this template only once by setting a global lock. *}
{if $skinVariables.typo && !isset($typoFormShown)}
  {$typoFormShown=true scope=global}
  <div id="typoModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="typoHtmlForm" method="post" onsubmit="return submitTypoForm();">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">{'report a typo'|_|capitalize}</h4>
          </div>

          <div class="modal-body">
            <textarea
              class="form-control"
              id="typoTextarea"
              cols="40"
              rows="3"
              maxlength="400"
              placeholder="{'please describe the error; at most 400 characters are allowed'|_}"
            ></textarea>
            <input type="hidden" name="definitionId" value="">

            <p class="voffset3">{'notes'|_|capitalize}:</p>

            <ul>
              <li>
                {'Some dictionaries (such as <em>Scriban</em>) use old
                orthographies. This is not a typo.'|_}
              </li>
              <li>
                {'We generally do not change the original text of definitions,
                but we can add comments. Please do not report semantic errors
                except in obvious cases.'|_}
              </li>
            </ul>
          </div>

          <div class="modal-footer">
            <button class="btn btn-primary" id="typoSubmit" type="submit">{'submit'|_}</button>
            <button class="btn btn-link" data-dismiss="modal">{'cancel'|_}</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {* Confirmation modal *}
  <div id="typoConfModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">{'report a typo'|_|capitalize}</h4>
        </div>

        <div class="modal-body">
          {'Thank you for your report!'|_}
        </div>

        <div class="modal-footer">
          <button class="btn btn-link" data-dismiss="modal">{'close'|_}</button>
        </div>
      </div>
    </div>
  </div>

  <script>
   $(function() {
     $('#typoModal, #typoConfModal').detach().appendTo('body');
   });
  </script>
{/if}
