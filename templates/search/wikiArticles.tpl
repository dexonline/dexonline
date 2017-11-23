{if count($wikiArticles)}
  <h3>
    <i class="glyphicon glyphicon-education"></i>
    Articole pe această temă:
  </h3>

  <ul>
    {foreach $wikiArticles as $wa}
      <li>
        <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
      </li>
    {/foreach}
  </ul>
{/if}
