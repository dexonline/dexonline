{extends "layout-admin.tpl"}

{block "title"}Descarcă cuvântul zilei{/block}

{block "content"}

  <h3>Descarcă cuvintele zilei - {$month}/{$year}</h3>

  <table class="table table-condensed">
    <thead>
      <tr>
        <th class="col-md-1">zi</th>
        <th class="col-md-2">cuvânt</th>
        <th class="col-md-9">motivul alegerii</th>
      </tr>
    </thead>

    <tbody>
      {foreach $wotdSet as $t}
        <tr>
          <td>{$t.wotd->displayDate|date_format:"%e %b"}</td>
          <td>
            <a href="https://dexonline.ro/definitie/{$t.def->id}">{$t.def->lexicon}</a>
          </td>
          <td>{$t.wotd->description}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>

{/block}
