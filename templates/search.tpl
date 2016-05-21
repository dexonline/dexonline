{extends file="page.tpl"}

{block name=title}
  {$cuv|escape} - definiție
  {if count($sourceList) == 1}{$sourceList[0]}{/if}
  {if $showParadigm}și paradigmă{/if}
{/block}

{block name=pageDescription}
  {if isset($pageDescription)}
    <meta name="description" content="{$pageDescription}"/>
  {/if}
{/block}

{block name=content}
  {assign var="declensionText" value=$declensionText|default:null}
  {assign var="exclude_unofficial" value=$exclude_unofficial|default:false}
  {assign var="ignoredWords" value=$ignoredWords|default:null}
  {assign var="lexems" value=$lexems|default:null}
  {assign var="lexemId" value=$lexemId|default:null}
  {assign var="lockExists" value=$lockExists|default:false}
  {assign var="onlyParadigm" value=$onlyParadigm|default:false}
  {assign var="results" value=$results|default:null}
  {assign var="showParadigm" value=$showParadigm|default:false}
  {assign var="stopWords" value=$stopWords|default:null}
  {assign var="wikiArticles" value=$wikiArticles|default:null}
  {assign var="totalDefinitionsCount" value=$totalDefinitionsCount|default:null}
  {assign var="allDefinitions" value=$allDefinitions|default:null}

  {if count($lexems) || count($results) }
    <p class="bg-info">
      {if $searchType == $smarty.const.SEARCH_INFLECTED}
        {if count($results) == 0}
          {if $src_selected}
            Nu am găsit în acest dicționar definiția lui
          {else}
            Din motive de copyright, doar administratorii site-ului pot vedea definițiile pentru
          {/if}
        {elseif count($results) == 1}
          O definiție pentru
        {else}
          {if $allDefinitions == 0 && $totalDefinitionsCount}
            Din <a href="{$smarty.server.REQUEST_URI}/expandat" title="arată toate definițiile">totalul de {$totalDefinitionsCount}</a> sunt afișate
          {/if}
          {$results|@count} definiții pentru
        {/if}

        {if count($lexems) == 1}
          {* If there is exactly one lexem, do not link to the lexem page, because it would print an almost exact duplicate of this page. *}
          „{include file="bits/lexemName.tpl" lexem=$lexems.0}”
        {else}
          {foreach from=$lexems item=lexem key=row_id}
            <a href="{$wwwRoot}lexem/{$lexem->formNoAccent}/{$lexem->id}">{$lexem->formNoAccent}</a
                                                                                                >{if $lexem->description} ({$lexem->description|escape}){/if
                                                                                                                                                        }{if $row_id < count($lexems) - 1},{/if}
          {/foreach}
        {/if}

      {elseif $searchType == $smarty.const.SEARCH_APPROXIMATE}
        {if count($lexems)}
          Cuvântul „{$cuv|escape}” nu a fost găsit, dar am găsit următoarele {$lexems|@count} cuvinte apropiate:
        {/if}

      {elseif $searchType == $smarty.const.SEARCH_DEF_ID}
        {if count($results)}
          Definiția cu ID-ul {$defId|escape}:
        {/if}

      {elseif $searchType == $smarty.const.SEARCH_REGEXP}
        {if $numResults}
          {if $numResults > count($lexems)}
            {$numResults} rezultate pentru „{$cuv|escape}” (maximum {$lexems|@count} afișate):
          {else}
            {$numResults} rezultate pentru „{$cuv|escape}”:
          {/if}
        {/if}

      {elseif $searchType == $smarty.const.SEARCH_LEXEM_ID}
        {if count($lexems) > 0}
          {if $exclude_unofficial}
            Lexemul cu ID-ul căutat există, dar este neoficial.
          {else}
            {if count($results) == 1}
              O definiție pentru
            {else}
              {$results|@count} definiții pentru
            {/if}
            „{include file="bits/lexemName.tpl" lexem=$lexems.0}”
          {/if}
        {/if}

      {elseif $searchType == $smarty.const.SEARCH_FULL_TEXT}
        {if $numResults == 1}
          O definiție cuprinde toate cuvintele căutate
        {elseif $numResults > 1}
          {$numResults} definiții cuprind toate cuvintele căutate
        {/if}

        {if $numResults > count($results)}
          (maximum {$results|@count} afișate)
        {/if}

      {elseif $searchType == $smarty.const.SEARCH_MULTIWORD}
        {$results|@count} definiții se potrivesc cu cel puțin doi dintre termenii căutați. Dacă rezultatele nu sunt mulțumitoare, puteți căuta cuvintele separat
        sau puteți căuta <a href="{$wwwRoot}text/{$cuv|escape:url}">în tot corpul definițiilor</a>.

      {/if}

      &nbsp;

      {if $declensionText}
        {if $onlyParadigm}
          {$declensionText}
        {else}
          <a class="inflLink"
             href="#"
             data-lexem-id="{$lexemId}"
             data-cuv="{$cuv|escape:url}"
             title="clic pentru conjugarea / declinarea cuvintelor">
            <span id="inflArrow">{if $showParadigm}&#x25bd;{else}&#x25b7;{/if}</span>
            {$declensionText}
          </a>
        {/if}
      {/if}

      {if !count($results) && count($lexems)}
        {if $src_selected}
          <br/>
          Repetați căutarea <a href="{$wwwRoot}definitie/{$cuv|escape}">în toate dicționarele</a>
        {/if}
      {/if}
    </p>
  {/if}

  {if $searchType == $smarty.const.SEARCH_FULL_TEXT && $lockExists}
    Momentan nu puteți căuta prin textul definițiilor, deoarece indexul este în curs de reconstrucție. Această operație durează de obicei circa
    10 minute. Ne cerem scuze pentru neplăcere.
  {/if}

  <div id="resultsWrapper" class="txt">
    {if $searchType != $smarty.const.SEARCH_REGEXP}
      <div id="paradigmDiv" {if !$showParadigm}style="display: none"{/if}>
        {if $showParadigm}{include file="bits/multiParadigm.tpl"}{/if}
      </div>
    {/if}

    {if !empty($images)}
      {include file="bits/gallery.tpl" images=$images}
    {/if}

    {if $stopWords}
      <span class="stopWords">
        Următoarele cuvinte au fost ignorate deoarece sunt prea comune:
        <b>
          {foreach from=$stopWords item=word}
            {$word|escape}
          {/foreach}
        </b>
      </span>
    {/if}

    {if $ignoredWords}
      <span class="stopWords">
        Sunt permise maximum 5 cuvinte. Următoarele cuvinte au fost ignorate:
        <b>
          {foreach from=$ignoredWords item=word}
            {$word|escape}
          {/foreach}
        </b>
      </span>
    {/if}

    {if $wikiArticles}
      <div class="wikiArticleLink">
        Articole pe această temă:
        {foreach from=$wikiArticles item=wa}
          <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
        {/foreach}
      </div>
    {/if}

    {assign var=notDisplayedUnofficial value=true}
    {assign var=notDisplayedSpec value=true}
    {foreach from=$results item=row key=i}
      {if $searchType != $smarty.const.SEARCH_FULL_TEXT }
        {if $row->source->isOfficial == 1 && $notDisplayedSpec}
          <h4>Definiții din dicționare specializate</h4><span class="h4Sub">Aceste definiții pot explica numai anumite înțelesuri ale cuvintelor.</span><br/>
          <hr/>
          {assign var=notDisplayedSpec value=false}
        {elseif $row->source->isOfficial == 0 && $notDisplayedUnofficial}
          <h4>Definiții din dicționare neoficiale</h4><span class="h4Sub">Deoarece nu sunt editate de lexicografi, aceste definiții pot conține erori, deci e preferabilă consultarea altor dicționare în paralel</span><br/>
          <hr/>
          {assign var=notDisplayedUnofficial value=false}
        {/if}
      {/if}
      {include file="bits/definition.tpl" row=$row}
    {/foreach}

    {if isset($hiddenSources) && count($hiddenSources) && !count($results)}
      Puteți găsi definiții pentru acest cuvânt în dicționarele:

      <li>
        {foreach from=$hiddenSources item=hs}
          <ul>{$hs->name}, {$hs->publisher}, {$hs->year}</ul>
        {/foreach}
      </li>
    {/if}

    {if $searchType == $smarty.const.SEARCH_APPROXIMATE || $searchType == $smarty.const.SEARCH_REGEXP}
      {foreach from=$lexems item=lexem key=row_id}
        {if $row_id}|{/if}
        <a href="{$wwwRoot}lexem/{$lexem->formNoAccent}/{$lexem->id}">{include file="bits/lexemName.tpl" lexem=$lexem}</a>
      {/foreach}
    {/if}

    {if $skinVariables.typo}
      <div id="typoDiv"></div>
      <script>
       $(".typoLink").click(showTypoForm);
      </script>
    {/if}
  </div>
{/block}
