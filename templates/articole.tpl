{extends "layout.tpl"}

{block "title"}Articole lingvistice{/block}

{block "content"}
  <h1>Articole lingvistice</h1>

  <div id="linguisticArticles">
    {foreach $wikiTitles as $k => $v}
      <h3>{$k|escape:'html'}</h3>
      <ul>
        {foreach $v as $titlePair}
          <li>
            <a href="{$wwwRoot}articol/{$titlePair[1]}">{$titlePair[0]}</a>
          </li>
        {/foreach}
      </ul>
    {/foreach}
  </div>
{/block}
