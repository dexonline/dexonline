{extends "layout-admin.tpl"}

{block "title"}Cuvinte necunoscute{/block}

{block "content"}
  <h3>Cuvinte necunoscute întâlnite în articole</h3>

  <div class="panel panel-default">
    <div class="panel-heading">Cele mai des întâlnite</div>

    <table class="table">
      <thead>
        <tr>
          <th>cuvânt</th>
          <th>număr de apariții</th>
        </tr>
      </thead>
      <tbody>
        {foreach $unknownWords as $uw}
          <tr>
            <td>{$uw->word}</td>
            <td>{$uw->count}</td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>

{/block}
