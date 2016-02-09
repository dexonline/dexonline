{extends file="layout.tpl"}

{block name=title}Alocare autori{/block}

{block name=content}
  <h3>Alocarea autorilor de imagini</h3>

  <p>
    Clic în tabelul stâng pentru a alege un autor, apoi clic pe oricare
    din zilele din tabelul drept pentru a asigna autorul ales.
  </p>

  <table id="artists">
    <thead>
      <tr>
        <th>
          autori
        </th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$artists item=a}
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

  <table id="calendar">
    <thead>
      <tr>
        <th colspan="2">
          <span class="monthNav prevMonth" data-delta="-1"></span>
          <span id="monthName"></span>
          <span class="monthNav nextMonth" data-delta="1"></span>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr id="stem" class="calendarRow">
        <td class="day"></td>
        <td class="artist"></td>
      </tr>
    </tbody>
  </table>

  <div class="clearer"></div>
{/block}
