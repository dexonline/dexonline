{extends file="layout.tpl"}

{block name=title}{$page_title}{/block}

{block name=content}
  <div>
    <p class="paragraphTitle">Cuvântul lunii {$timestamp|date_format:'%B %Y'}</p>
  </div>

  {include file="bits/definition.tpl" row=$searchResult}

  <div id="wotdPrevNext">
    {if $prevmon}<div class="prev"><a href="{$wwwRoot}cuvantul-lunii/{$prevmon}">« precedentul</a></div>{/if}
    {if $nextmon}<div class="next"><a href="{$wwwRoot}cuvantul-lunii/{$nextmon}">următorul »</a></div>{/if}
    <div style="clear: both;"></div>
  </div>

  {if $imageUrl}
    <div id="wotdImage">
      <img src="{$imageUrl}" alt="{$searchResult->definition->lexicon}" title="{$searchResult->definition->lexicon}"/>
      <div class="copyright">
        {if $imageCredits}{$imageCredits}{/if}
      </div>
    </div>
  {/if}

  {*
     {if $skinVariables.wotdArchive}
     <p class="paragraphTitle">Arhiva cuvintelor lunii</p>

     <div id="wotmArchive" class="wotmArchive"></div>
     <script>loadAjaxContent('{$wwwRoot}arhiva/cuvantul-lunii/{$timestamp|date_format:'%Y'}','#wotmArchive')</script>

     {/if}
   *}
{/block}
