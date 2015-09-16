{extends file="layout.tpl"}

{block name=title}{$wa->title|default:'Articol inexistent'}{/block}

{block name=content}
  {assign var="wa" scope=global value=$wa|default:null}
  {assign var="title" value=$wa->title|default:null}

  {if $title == "Ghid de exprimare corectă"}
    <div id="guideNotice">
      Ghidul de exprimare are o structură nouă. <a href="#" onclick="return false;">detalii</a>
    </div>
    <p id="guideInfo">
      Pentru a trata mai bine unele probleme de exprimare corectă, am înlocuit ghidul de exprimare cu o secțiune de <a href="../articole">articole pe teme
      lingvistice</a>. Meniul <i>Resurse</i> conține o legătură către lista articolelor. La căutarea în <i>dexonline</i> pot apărea legături către
      articolele relevante, acolo unde avem articole mai ample pe marginea cuvântului căutat.
      Intenția noastră este să împărțim acest ghid în articole pe diverse teme și, în timp, să cooptăm lingviști profesioniști pentru a îmbunătăți calitatea articolelor.
    </p>

    <script type="text/javascript">
     jQuery(document).ready(function() {ldelim}
             jQuery("#guideInfo").hide();
         jQuery("#guideNotice").click(function() {ldelim} jQuery(this).next("#guideInfo").slideToggle(200); {rdelim});
         {rdelim});
    </script>
  {/if}

  {if $wa}
    <p class="paragraphTitle">{$wa->title}</p>
    <div class="wikiArticle">{$wa->htmlContents}</div>
  {else}
    <p class="paragraphTitle">Articol inexistent</p>
    <div class="wikiArticle">Articolul pe care îl căutați nu există.</div>
  {/if}

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
