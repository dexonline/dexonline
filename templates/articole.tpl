{extends "layout.tpl"}

{block "title"}Articole lingvistice{/block}

{block "content"}
  <h1>Articole lingvistice</h1>

  <div id="linguisticArticles">
    {foreach $wikiTitles as $section => $articles}
      <h3>{$section|escape:'html'}</h3>
      <ul>
        {foreach $articles as $wa}
          <li>
            <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
          </li>
        {/foreach}
      </ul>
    {/foreach}
  </div>
{/block}
