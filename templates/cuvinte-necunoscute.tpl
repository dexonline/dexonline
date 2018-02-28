{extends "layout-admin.tpl"}

{block "title"}Cuvinte necunoscute{/block}

{block "content"}
  <h3>Cuvinte necunoscute întâlnite în articole ({$numUnknownWords})</h3>

  <div class="panel panel-default">
    <div class="panel-heading">Cele mai frecvente</div>

    <table class="table">
      <thead>
        <tr>
          <th>cuvânt</th>
          <th>#&nbsp;apariții</th>
          <th>exmple</th>
        </tr>
      </thead>
      <tbody>
        {foreach $unknownWords as $uw}
          <tr>
            <td>{$uw->word}</td>
            <td>{$uw->count}</td>
            <td>
              {foreach $uw->examples as $u}
                <div>
                  {$u.context}
                  [<a href="{$u.crawlerUrl->url}">{$u.crawlerUrl->siteId}</a>]
                </div>
              {/foreach}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>

{/block}
