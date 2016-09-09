{extends file="layout.tpl"}

{block name=title}{$wa->title|default:'Articol inexistent'}{/block}

{block name=content}
  {assign var="wa" scope=global value=$wa|default:null}
  {assign var="title" value=$wa->title|default:'Articol inexistent'}

  <h3>{$wa->title}</h3>

  <div>
    {$wa->htmlContents|default:'Articolul pe care îl căutați nu există.'}
  </div>

  <hr>

  <h3>Alte articole lingvistice</h3>

  {foreach $wikiTitles as $k => $v}
    <h4>{$k|escape:'html'}</h4>
    <ul>
      {foreach $v as $titlePair}
        {if $titlePair[0] != $title}
          <li>
            <a href="{$wwwRoot}articol/{$titlePair[1]}">{$titlePair[0]}</a>
          </li>
        {/if}
      {/foreach}
    </ul>
  {/foreach}

{/block}
