<h3>
  {t
    count=count($wikiArticles)
    1=count($wikiArticles)
    plural="%1 linguistics articles"}
  One linguistics article{/t}
</h3>

<ul>
  {foreach $wikiArticles as $wa}
    <li>
      <a href="{Router::link('article/view')}/{$wa->getUrlTitle()}">{$wa->title}</a>
    </li>
  {/foreach}
</ul>
