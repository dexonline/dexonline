{if count($wikiArticles)}
  <div class="card card-body mt-3 pb-0">
    <h3>
      {include "bits/icon.tpl" i=school}
      {t}Articles on this subject{/t}:
    </h3>

    <ul>
      {foreach $wikiArticles as $wa}
        <li>
          <a href="{Router::link('article/view')}/{$wa->getUrlTitle()}">{$wa->title}</a>
        </li>
      {/foreach}
    </ul>
  </div>
{/if}
