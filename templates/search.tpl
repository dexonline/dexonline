{extends "layout.tpl"}

{block "title"}
  {$cuv|escape} - {t}definition{/t}
  {if count($sourceList) == 1}{$sourceList[0]}{/if}
  {if $searchParams.paradigm}{t}and paradigm{/t}{/if}
{/block}

{block "pageDescription"}
  {if isset($pageDescription)}
    <meta name="description" content="{$pageDescription}">
  {/if}
{/block}

{block "banner"}{/block}

{block "content"}
  {assign var="declensionText" value=$declensionText|default:null}
  {assign var="tab" value=$tab|default:false}

  {include "banner/banner.tpl"}

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" {if $tab == Constant::TAB_RESULTS}class="active"{/if}>
      <a href="#resultsTab" aria-controls="resultsTab" role="tab" data-toggle="tab">
        {t}results{/t} ({$extra.numResults})
      </a>
    </li>

    {if $searchParams.paradigm}
      <li role="presentation" {if $tab == Constant::TAB_PARADIGM}class="active"{/if}>
        <a href="#paradigmTab" aria-controls="paradigmTab" role="tab" data-toggle="tab">
          {$declensionText}
        </a>
      </li>
    {/if}

    {if count($trees)}
      <li role="presentation" {if $tab == Constant::TAB_TREE}class="active"{/if}>
        <a href="#treeTab" aria-controls="treeTab" role="tab" data-toggle="tab">
          {t}synthesis{/t} ({count($trees)})
        </a>
      </li>
    {/if}
  </ul>

  <div class="tab-content">
    {* results tab *}
    <div
      role="tabpanel"
      class="tab-pane {if $tab == Constant::TAB_RESULTS}active{/if}"
      id="resultsTab">

      {* definition ID search *}
      {if $searchType == $smarty.const.SEARCH_DEF_ID}
        <h3>{t}Definition with ID{/t} {$results|array_keys|implode}:</h3>

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
              none="{t}No definitions contain{/t}"
              one="{t}One definition contains{/t}"
              many="{t}definitions contain{/t}"
              common="{t}all the words{/t}"}
          </h3>

          {if !empty($extra.stopWords)}
            <p class="text-warning">
              {t}The following words were ignored because they are too common:{/t}
              <strong>
                {' '|implode:$extra.stopWords|escape}
              </strong>
            </p>
          {/if}

          {include "search/definitionList.tpl" categories=false}
        {/if}

        {* entry ID search *}
      {elseif $searchType == $smarty.const.SEARCH_ENTRY_ID}

        {include "search/gallery.tpl"}

        {if !count($entries)}
          <h3>{t}There is no entry with the given ID.{/t}</h3>
        {else}

          <h3>
            {include "bits/count.tpl"
              displayed=count($results)
              none="{t}No definitions{/t}"
              one="{t}One definition{/t}"
              many="{t}definitions{/t}"
              common="{t}for{/t}"}

            {include "bits/entry.tpl" entry=$entries[0] variantList=true tagList=true}
          </h3>

          {include "search/missingDefinitionWarnings.tpl"}
          {include "search/definitionList.tpl"}
        {/if}

        {* regular expression search *}
      {elseif $searchType == $smarty.const.SEARCH_REGEXP}
        {capture "common"}
        {t}for{/t} <strong>{$cuv|escape}</strong>
        {/capture}

        <h3>
          {include "bits/count.tpl"
            displayed=count($lexemes)
            total=$extra.numLexemes|default:0
            none="{t}No results{/t}"
            one="{t}One result{/t}"
            many="{t}results{/t}"
            common=$smarty.capture.common}
        </h3>

        {if !count($lexemes) && $sourceId}
          {include "search/extendToAllSources.tpl"}
        {/if}

        {include "search/lexemeList.tpl"}

        {* normal search (inflected form search) *}
      {elseif $searchType == $smarty.const.SEARCH_INFLECTED}

        {include "search/gallery.tpl"}

        {if count($entries) > 1}
          <h3>{$entries|count} {t}entries{/t}</h3>

          {include "search/entryToc.tpl"}
        {else}
          {capture "common"}
          {t}for{/t} {include "bits/entry.tpl" entry=$entries[0] variantList=true tagList=true}
          {/capture}

          <h3>
            {include "bits/count.tpl"
              displayed=count($results)
              total=$extra.numDefinitions
              none="{t}No definitions{/t}"
              one="{t}One definition{/t}"
              many="{t}definitions{/t}"
              common=$smarty.capture.common}
          </h3>

          {if !count($results) && count($entries) && $sourceId}
            {include "search/extendToAllSources.tpl"}
          {/if}
        {/if}

        {include "search/wikiArticles.tpl"}

        {* another <h3> for the definition list, if needed *}
        {if (count($entries) > 1) && count($results)}
          <h3>
            {include "bits/count.tpl"
              displayed=count($results)
              total=$extra.numDefinitions
              none=""
              one="{t}One definition{/t}"
              many="{t}definitions{/t}"
              common=""}
          </h3>
        {/if}

        {include "search/missingDefinitionWarnings.tpl"}

        {include "search/showAllLink.tpl"}
        {include "search/definitionList.tpl"}
        {include "search/showAllLink.tpl"}

        {* multiword search *}
      {elseif $searchType == $smarty.const.SEARCH_MULTIWORD}
        <h3>
          {include "bits/count.tpl"
            displayed=count($results)
            total=$extra.numDefinitions
            none="{t}No definitions match{/t}"
            one="{t}One definition matches{/t}"
            many="{t}definitions match{/t}"
            common="{t}at least two words{/t}"}
        </h3>

        <p class="text-warning">
          {t}If the results are inadequate, you can look up individual words or you can search{/t}
          <a href="{$wwwRoot}text/{$cuv|escape:url}">{t}full-text{/t}</a>.
        </p>

        {if !empty($extra.ignoredWords)}
          <p class="text-warning">
            {t}At most 5 words are allowed. The following words were ignored:{/t}
            <strong>
              {' '|implode:$extra.ignoredWords|escape}
            </strong>
          </p>
        {/if}

        {include "search/showAllLink.tpl"}
        {include "search/definitionList.tpl" categories=false}
        {include "search/showAllLink.tpl"}

        {* approximate search *}
      {elseif $searchType == $smarty.const.SEARCH_APPROXIMATE}
        {if count($entries)}
          <h3>
            {'The word <strong>%s</strong> is not in the dictionary.
            Here are some suggestions:'|_|sprintf:($cuv|escape)}
          </h3>

          {include "search/entryList.tpl"}
        {else}
          <h3>{'No results for <strong>%s</strong>'|_|sprintf:($cuv|escape)}</h3>
        {/if}

      {/if}

    </div>

    {* paradigm tab *}
    {if $searchParams.paradigm}
      <div
        role="tabpanel"
        class="tab-pane {if $tab == Constant::TAB_PARADIGM}active{/if}"
        id="paradigmTab">

        {foreach $entries as $e}
          {include "bits/multiParadigm.tpl" entry=$e}
        {/foreach}

        {if $hasUnrecommendedForms}
          <div class="notRecommendedLegend">
            * {t}unrecommended or incorrect form{/t} –
            <a id="toggleNotRecommended"
              href="#"
              class="doubleText"
              data-other-text="({t}hide{/t})">
              ({t}show{/t})
            </a>
          </div>
        {/if}

        {if $hasElisionForms}
          {if User::can(User::PRIV_EDIT)}
            {$text1='hide'|_}
            {$text2='show'|_}
          {else}
            {$text1='show'|_}
            {$text2='hide'|_}
          {/if}
          <div class="elisionLegend">
            * {t}elisions{/t} –
            <a id="toggleElision"
              href="#"
              class="doubleText"
              data-other-text="({$text2})">
              ({$text1})
            </a>
          </div>
        {/if}

        <div class="paradigmLink voffset2">
          <a title="{t}link to this inflected forms page{/t}"
            href="{$paradigmLink}">
            <i class="glyphicon glyphicon-link"></i>
            {t}link to this paradigm{/t}
          </a>
        </div>
      </div>
    {/if}

    {* tree tab *}
    {if count($trees)}
      <div
        role="tabpanel"
        class="tab-pane {if $tab == Constant::TAB_TREE}active{/if}"
        id="treeTab">
        {include "search/trees.tpl"}
      </div>
    {/if}

  </div>
{/block}
