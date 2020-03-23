{* Include this template only once by setting a global lock. *}
{if Config::SKIN_TYPO && !isset($typoFormShown)}
  {$typoFormShown=true scope=global}
  <div id="typoModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="typoHtmlForm" method="post" onsubmit="return submitTypoForm();">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">{cap}{t}report a typo{/t}{/cap}</h4>
          </div>

          <div class="modal-body">
            <textarea
              class="form-control"
              id="typoTextarea"
              cols="40"
              rows="3"
              maxlength="400"
              placeholder="{t}please describe the error; at most 400 characters are allowed{/t}"
            ></textarea>
            <input type="hidden" name="definitionId" value="">

            <p class="voffset3">{cap}{t}notes{/t}{/cap}:</p>

            <ul>
              <li>
                <strong>
                  {t}Some dictionaries (such as <em>Scriban</em>) use old orthographies.
                  This is not a typo.{/t}
                </strong>
              </li>
              <li>
                {t}We generally do not change the original text of
                definitions, but we can add comments. Please do not report
                semantic errors except in obvious cases.{/t}
              </li>
            </ul>
          </div>

          <div class="modal-footer">
            <button class="btn btn-primary" id="typoSubmit" type="submit">{t}submit{/t}</button>
            <button class="btn btn-link" data-dismiss="modal">{t}cancel{/t}</button>
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
          <h4 class="modal-title">{cap}{t}report a typo{/t}{/cap}</h4>
        </div>

        <div class="modal-body">
          {t}Thank you for your report!{/t}
        </div>

        <div class="modal-footer">
          <button class="btn btn-link" data-dismiss="modal">{t}close{/t}</button>
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
