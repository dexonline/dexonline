{if count($wikiArticles)}
  <h3>
    <i class="glyphicon glyphicon-education"></i>
    {'Articles on this subject'|_}:
  </h3>

  <ul>
    {foreach $wikiArticles as $wa}
      <li>
        <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
      </li>
    {/foreach}
  </ul>
{/if}
