{extends "layout-admin.tpl"}

{block "title"}Alocare autori{/block}

{block "content"}
  <h3>Alocarea autorilor de imagini</h3>

  <p>
    Clic în tabelul stâng pentru a alege un autor, apoi clic pe oricare
    din zilele din tabelul drept pentru a asigna autorul ales.
  </p>

  <div class="row">
    <div class="col-sm-6">
      <table class="table table-hover table-responsive table-condensed table-bordered" id="artists">
        <caption class="table-caption text-center">autori</caption>
        <tbody>
          {foreach $artists as $a}
            <tr class="artistRow" data-id="{$a->id}">
              <td>
                {$a->name}
              </td>
            </tr>
          {/foreach}
          <tr class="artistRow" data-id="0">
            <td>
              nimeni
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col-sm-6">
      <table class="table table-responsive table-condensed table-bordered" id="calendar">
        <caption class="table-caption text-center">
          <span class="pull-left glyphicon glyphicon-chevron-left monthNav prevMonth" data-delta="-1"></span>
          <span id="monthName"></span>
          <span class="pull-right glyphicon glyphicon-chevron-right monthNav nextMonth" data-delta="1"></span>
        </caption>
        <tbody>
          <tr id="stem" class="calendarRow">
            <td class="day"></td>
            <td class="artist"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

{/block}
