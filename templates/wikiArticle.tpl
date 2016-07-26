{extends file="layout.tpl"}

{block name=title}{$wa->title|default:'Articol inexistent'}{/block}

{block name=content}
  {assign var="wa" scope=global value=$wa|default:null}
  {assign var="title" value=$wa->title|default:'Articol inexistent'}

  <h3>{$wa->title}</h3>

  <div class="wikiArticle">
    {$wa->htmlContents|default:'Articolul pe care îl căutați nu există.'}
  </div>

  <hr>

  <h3>Alte articole lingvistice</h3>

  {foreach from=$wikiTitles key=k item=v}
    <h4>{$k|escape:'html'}</h4>
    {foreach from=$v item=titlePair}
      {if $titlePair[0] != $title}
        <a href="{$wwwRoot}articol/{$titlePair[1]}">{$titlePair[0]}</a><br/>
      {/if}
    {/foreach}
  {/foreach}

  <script>
   $(tablesorterMediaWikiInit);
  </script>
{/block}
