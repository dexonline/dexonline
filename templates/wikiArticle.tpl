{extends "layout.tpl"}

{block "title"}{$wa->title|default:'Articol inexistent'}{/block}

{block "content"}
  {assign var="wa" scope=global value=$wa|default:null}
  {assign var="title" value=$wa->title|default:'Articol inexistent'}

  <h3>{$wa->title}</h3>

  <div>
    {$wa->htmlContents|default:'Articolul pe care îl căutați nu există.'}
  </div>

  <hr>

  <h3>{'Other linguistic articles'|_}</h3>

  {foreach $wikiTitles as $section => $articles}
    <h4>{$section|escape:'html'}</h4>
    <ul>
      {foreach $articles as $wa}
        {if $wa->title != $title}
          <li>
            <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
          </li>
        {/if}
      {/foreach}
    </ul>
  {/foreach}

{/block}
