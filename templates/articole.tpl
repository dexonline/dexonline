{extends "layout.tpl"}

{block "title"}{cap}{t}linguistic articles{/t}{/cap}{/block}

{block "content"}
  <h1>{cap}{t}linguistic articles{/t}{/cap}</h1>

  <div id="linguisticArticles">
    {foreach $wikiTitles as $section => $articles}
      <h3>{$section|escape:'html'}</h3>
      <ul>
        {foreach $articles as $wa}
          <li>
            <a href="articol/{$wa->getUrlTitle()}">{$wa->title}</a>
          </li>
        {/foreach}
      </ul>
    {/foreach}
  </div>
{/block}
