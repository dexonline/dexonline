{if count($wikiArticles)}
  <div class="panel panel-default">
    <div class="panel-heading">Articole pe această temă:</div>
    <div class="panel-body">
      <ul>
        {foreach $wikiArticles as $wa}
          <li>
            <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
          </li>
        {/foreach}
      </ul>
    </div>
  </div>
{/if}
