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
            <div class="donation-wrapper">
              <h4>Donație de la {$donor->email}, {$donor->amount} de lei, {$donor->date}</h4>

              {include "bs/checkbox.tpl"
                name='processTicketId[]'
                label='salvează donația și închide tichetul'
                checked=true
                divClass='mb-2'
                value=$donor->ticketId}

              {if $donor->needsEmail() == Donor::EMAIL_YES}
                {include "bs/checkbox.tpl"
                  name='messageTicketId[]'
                  label='trimite un mesaj cu textul:'
                  checked=true
                  divClass='mb-2'
                  value=$donor->ticketId}

                <div class="card card-body mb-2">
                  {$donor->htmlMessage}
                </div>
                <div class="card card-body mb-3">
                  <pre>{$donor->textMessage}</pre>
                </div>
              {else}
                <p class="text-muted">
                  {$donor->getEmailReason()}
                </p>
              {/if}
            </div>
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
              {include "bs/checkbox.tpl"
                name="manualSendMessage_{$i}"
                label='trimite un mesaj cu textul:'
                checked=true
                divClass='mb-2'}

              <div class="card card-body mb-2">
                {$donor->htmlMessage}
              </div>
              <div class="card card-body">
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
