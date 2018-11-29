{if count($wikiArticles)}
  <h3>
    <i class="glyphicon glyphicon-education"></i>
    {t}Articles on this subject{/t}:
  </h3>

  <ul>
    {foreach $wikiArticles as $wa}
      <li>
        <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
      </li>
    {/foreach}
  </ul>
{/if}
