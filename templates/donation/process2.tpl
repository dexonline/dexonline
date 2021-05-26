{extends "layout-admin.tpl"}

{block "title"}
  Procesează donații
{/block}

{block "content"}
  <h3>Procesează donații</h3>

  <form class="form" method="post">
    {if $includeOtrs}
      <input type="hidden" name="includeOtrs" value="1">
    {/if}

    {if !empty($otrsDonors)}
      <div class="card mb-3">
        <div class="card-header">Donații OTRS</div>
        <div class="card-body">
          {foreach $otrsDonors as $donor}
            <h4>Donație de la {$donor->email}, {$donor->amount} de lei, {$donor->date}</h4>

            <div class="form-check mb-2">
              <label class="form-check-label">
                <input
                  type="checkbox"
                  class="form-check-input"
                  name="processTicketId[]"
                  value="{$donor->ticketId}"
                  checked>
                trimite un mesaj cu textul:
              </label>
            </div>

            {if $donor->needsEmail() == Donor::EMAIL_YES}
              <div class="form-check mb-2">
                <label class="form-check-label">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    name="messageTicketId[]"
                    value="{$donor->ticketId}"
                    checked>
                  trimite un mesaj cu textul:
                </label>
              </div>

              <div class="card card-body bg-light mb-2">
                {$donor->htmlMessage}
              </div>
              <div class="card card-body bg-light">
                <pre>{$donor->textMessage}</pre>
              </div>
            {else}
              <p class="text-muted">
                {$donor->getEmailReason()}
              </p>
            {/if}
          {/foreach}
        </div>
      </div>
    {/if}

    {if count($manualDonors)}
      <div class="card mb-3">
        <div class="card-header">Donații introduse manual</div>
        <div class="card-body">
          {foreach $manualDonors as $i => $donor}
            <h4>Donație de la {$donor->email}, {$donor->amount} de lei, {$donor->date}</h4>

            <input type="hidden" name="email[]" value="{$donor->email}">
            <input type="hidden" name="amount[]" value="{$donor->amount}">
            <input type="hidden" name="date[]" value="{$donor->date}">

            {if $donor->needsEmail() == Donor::EMAIL_YES}
              <div class="form-check mb-2">
                <label class="form-check-label">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    name="manualSendMessage_{$i}"
                    value="1"
                    checked>
                  trimite un mesaj cu textul:
                </label>
              </div>

              <div class="card card-body bg-light mb-2">
                {$donor->htmlMessage}
              </div>
              <div class="card card-body bg-light">
                <pre>{$donor->textMessage}</pre>
              </div>
            {else}
              <p class="text-muted">
                {$donor->getEmailReason()}
              </p>
            {/if}
          {/foreach}
        </div>
      </div>
    {/if}

    {if empty($otrsDonors) && empty($manualDonors)}
      <p>
        Nimic de făcut.
      </p>
    {/if}

    <div>
      {if !empty($otrsDonors) || !empty($manualDonors)}
        <button type="submit" class="btn btn-primary" name="processButton">
          {include "bits/icon.tpl" i=done}
          procesează
        </button>
      {/if}

      <button type="submit" class="btn btn-link" name="backButton">
        {include "bits/icon.tpl" i=arrow_back}
        înapoi
      </button>
    </div>

  </form>
{/block}
