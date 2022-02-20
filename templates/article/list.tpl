{extends "layout.tpl"}

{block "title"}{cap}{t}linguistics articles{/t}{/cap}{/block}

{block "content"}
  <h1>{cap}{t}linguistics articles{/t}{/cap}</h1>

  <div id="linguisticArticles">
    {foreach $wikiTitles as $section => $articles}
      <h3>{$section|escape:'html'}</h3>
      <ul>
        {foreach $articles as $wa}
          <li>
            <a href="{Router::link('article/view')}/{$wa->getUrlTitle()}">{$wa->title}</a>
          </li>
        {/foreach}
      </ul>
    {/foreach}
  </div>
{/block}
