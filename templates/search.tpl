{extends "layout.tpl"}

{block "title"}
  {$cuv|escape} - definiție
  {if count($sourceList) == 1}{$sourceList[0]}{/if}
  {if $showParadigm}și paradigmă{/if}
{/block}

{block "pageDescription"}
  {if isset($pageDescription)}
    <meta name="description" content="{$pageDescription}"/>
  {/if}
{/block}

{block "content"}
  {assign var="declensionText" value=$declensionText|default:null}
  {assign var="showParadigm" value=$showParadigm|default:false}

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" {if !$showParadigm}class="active"{/if}>
      <a href="#resultsTab" aria-controls="resultsTab" role="tab" data-toggle="tab">
        rezultate
      </a>
    </li>

    {if $searchParams.paradigm}
      <li role="presentation" {if $showParadigm}class="active"{/if}>
        <a href="#paradigmTab" aria-controls="paradigmTab" role="tab" data-toggle="tab">
          {$declensionText}
        </a>
      </li>
    {/if}

    {if count($structuredResults)}
      <li role="presentation">
        <a href="#structuredTab" aria-controls="structuredTab" role="tab" data-toggle="tab">
          alte definiții
        </a>
      </li>
    {/if}
  </ul>

  <div class="tab-content">
    {* results tab *}
    <div role="tabpanel" class="tab-pane {if !$showParadigm}active{/if}" id="resultsTab">

      {* definition ID search *}
      {if $searchType == $smarty.const.SEARCH_DEF_ID}
        <h3>Definiția cu ID-ul {$results|array_keys|implode}:</h3>

        {include "search/definitionList.tpl"}

      {* full-text search *}
      {elseif $searchType == $smarty.const.SEARCH_FULL_TEXT}
        {if isset($extra.fullTextLock)}
          {include "search/fullTextLock.tpl"}
        {else}
          <h3>
            {include "bits/count.tpl"
            displayed=count($results)
            total=$extra.numDefinitionsFullText
            none="Nicio definiție nu cuprinde"
            one="O definiție cuprinde"
            many="definiții cuprind"
            common="toate cuvintele căutate"}
          </h3>

          {if !empty($extra.stopWords)}
            <p class="text-warning">
              Următoarele cuvinte au fost ignorate deoarece sunt prea comune:
              <strong>
                {' '|implode:$extra.stopWords|escape}
              </strong>
            </p>
          {/if}

          {include "search/definitionList.tpl" categories=false}
        {/if}

      {* entry ID search *}
      {elseif $searchType == $smarty.const.SEARCH_ENTRY_ID}
        {if !count($entries)}
          <h3>Nu există nicio intrare cu ID-ul căutat.</h3>
        {else}

          {if count($trees)}
            <h3>{include "bits/entry.tpl" entry=$entries[0] variantList=true}</h3>

            {include "search/trees.tpl"}

            {if count($results)}
              <h3>
                {include "bits/count.tpl"
                displayed=count($results)
                none=""
                one="O definiție"
                many="definiții"
                common=""}
              </h3>
            {/if}
          {else}

            <h3>
              {include "bits/count.tpl"
              displayed=count($results)
              none="Nicio definiție"
              one="O definiție"
              many="definiții"
              common="pentru"}

              {include "bits/entry.tpl" entry=$entries[0] variantList=true}
            </h3>
          {/if}

          {include "search/gallery.tpl"}
          {include "search/missingDefinitionWarnings.tpl"}
          {include "search/definitionList.tpl"}
        {/if}

      {* regular expression search *}
      {elseif $searchType == $smarty.const.SEARCH_REGEXP}
        {capture "common"}
          pentru <strong>{$cuv|escape}</strong>
        {/capture}

        <h3>
          {include "bits/count.tpl"
          displayed=count($lexems)
          total=$extra.numLexems|default:0
          none="Niciun rezultat"
          one="Un rezultat"
          many="rezultate"
          common=$smarty.capture.common}
        </h3>

        {if !count($lexems) && $sourceId}
          {include "search/extendToAllSources.tpl"}
        {/if}

        {include "search/lexemList.tpl"}

      {* normal search (inflected form search) *}
      {elseif $searchType == $smarty.const.SEARCH_INFLECTED}
        {if count($entries) > 1}
          <h3>{$entries|count} intrări</h3>

          {include "search/entryToc.tpl"}
        {else if count($trees)}
          <h3>{include "bits/entry.tpl" entry=$entries[0] variantList=true}</h3>
        {else}
          {capture "common"}
          pentru {include "bits/entry.tpl" entry=$entries[0] variantList=true}
          {/capture}

          <h3>
            {include "bits/count.tpl"
            displayed=count($results)
            total=$extra.numDefinitions
            none="Nicio definiție"
            one="O definiție"
            many="definiții"
            common=$smarty.capture.common}
          </h3>

          {if !count($results) && !count($structuredResults) && count($entries) && $sourceId}
            {include "search/extendToAllSources.tpl"}
          {/if}

        {/if}

        {include "search/wikiArticles.tpl"}
        {include "search/gallery.tpl"}
        {include "search/trees.tpl"}

        {* another <h3> for the definition list, if needed *}
        {if count($trees) || (count($entries) > 1)}
          {if count($results)}
            <h3>
              {include "bits/count.tpl"
              displayed=count($results)
              total=$extra.numDefinitions
              none=""
              one="O definiție"
              many="definiții"
              common=""}
            </h3>
          {/if}
        {/if}

        {include "search/missingDefinitionWarnings.tpl"}

        {include "search/showAllLink.tpl"}
        {include "search/definitionList.tpl"}

      {* multiword search *}
      {elseif $searchType == $smarty.const.SEARCH_MULTIWORD}
        <h3>
          {include "bits/count.tpl"
          displayed=count($results)
          total=$extra.numDefinitions
          none="Nicio definiție nu se potrivește"
          one="O definiție se potrivește"
          many="definiții se potrivesc"
          common="cu cel puțin doi dintre termenii căutați"}
        </h3>

        <p class="text-warning">
          Dacă rezultatele nu sunt mulțumitoare, puteți căuta cuvintele separat sau puteți căuta

          <a href="{$wwwRoot}text/{$cuv|escape:url}">
            în tot corpul definițiilor
          </a>.
        </p>

        {if !empty($extra.ignoredWords)}
          <p class="text-warning">
            Sunt permise maximum 5 cuvinte. Următoarele cuvinte au fost ignorate:
            <strong>
              {' '|implode:$extra.ignoredWords|escape}
            </strong>
          </p>
        {/if}

        {include "search/showAllLink.tpl"}
        {include "search/definitionList.tpl" categories=false}

      {* approximate search *}
      {elseif $searchType == $smarty.const.SEARCH_APPROXIMATE}
        {if count($entries)}
          <h3>
            Cuvântul <strong>{$cuv|escape}</strong> nu este în dicționar. Iată câteva sugestii:
          </h3>

          {include "search/entryList.tpl"}
        {else}
          <h3>Niciun rezultat pentru <strong>{$cuv|escape}</strong></h3>
        {/if}

      {/if}

    </div>

    {* paradigm tab *}
    {if $searchParams.paradigm}
      <div role="tabpanel" class="tab-pane {if $showParadigm}active{/if}" id="paradigmTab">
        {foreach $entries as $e}
          {include "bits/multiParadigm.tpl" entry=$e}
        {/foreach}

        {if $hasUnrecommendedForms}
          <div class="notRecommendedLegend">
            * Formă nerecomandată sau greșită –
            <span class="notRecommendedShowHide">(arată)</span>
          </div>
        {/if}

        <div>
          <a class="paradigmLink"
             title="Link către această pagină, dar cu flexiunile expandate"
             href="{$paradigmLink}">
            Link către această paradigmă
          </a>
        </div>
      </div>
    {/if}

    {* structured definitions tab *}
    {if count($structuredResults)}
      <div role="tabpanel" class="tab-pane" id="structuredTab">

        <div class="callout callout-info">
          <h3>
            {include "bits/count.tpl"
            displayed=count($structuredResults)
            none="Nicio definiție încorporată"
            one="O definiție încorporată"
            many="definiții încorporate"
            common=""}
          </h3>

          <p class="text-muted">
            Aceste definiții sunt deja încorporate în filele „rezultate” și
            „{$declensionText|default:"declinări"}”. Le prezentăm pentru edificare.
          </p>
        </div>

        {include "search/definitionList.tpl" results=$structuredResults}

      </div>
    {/if}
  </div>
{/block}
