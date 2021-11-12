{extends "layout-admin.tpl"}

{block "title"}Alocare autori{/block}

{block "content"}
  <h3>Alocarea autorilor de imagini</h3>

  <p>
    Clic în tabelul stâng pentru a alege un autor, apoi clic pe oricare
    din zilele din tabelul drept pentru a asigna autorul ales.
  </p>

  <div class="row">
    <div class="col">
      <table class="table table-hover" id="artists">
        <thead>
          <tr>
            <th scope="col">autori</th>
          </tr>
        </thead>
        <tbody>
          {foreach $artists as $a}
          {if not $a->hidden}
            <tr class="artistRow" data-id="{$a->id}">
              <td>
                {$a->name}
              </td>
            </tr>
          {/if}
          {/foreach}
          <tr class="artistRow" data-id="0">
            <td>
              nimeni
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="col">
      <table class="table table-sm table-hover" id="calendar">
        <thead>
          <tr>
            <th colspan="2">
              <div class="d-flex justify-content-between">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary py-0 monthNav"
                  data-delta="-1">
                  {include "bits/icon.tpl" i=chevron_left}
                </button>
                <span id="monthName"></span>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary py-0 monthNav"
                  data-delta="1">
                  {include "bits/icon.tpl" i=chevron_right}
                </button>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr id="stem" class="calendarRow">
            <td class="day w-25"></td>
            <td class="artist"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

{/block}
