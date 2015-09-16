{extends file="layout.tpl"}

{block name=title}{$wa->title|default:'Articol inexistent'}{/block}

{block name=content}
  {assign var="wa" scope=global value=$wa|default:null}
  {assign var="title" value=$wa->title|default:null}

  <p class="paragraphTitle">
    {$wa->title|default:'Articol inexistent'}
  </p>
  <div class="wikiArticle">
    {$wa->htmlContents|default:'Articolul pe care îl căutați nu există.'}
  </div>

  <p class="paragraphTitle">Alte articole lingvistice</p>

  {foreach from=$wikiTitles key=k item=v}
    <h3>{$k|escape:'html'}</h3>
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
