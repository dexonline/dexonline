{extends "layout.tpl"}

{block "title"}
  {$cuv=$cuv|escape}
  {$cuv} - {t}definition{/t}
  {if count($sourceList) == 1}{$sourceList[0]}{/if}
  {if $searchParams.paradigm}{t}and paradigm{/t}{/if}
{/block}

{block "pageDescription"}
  {if isset($pageDescription)}
    <meta name="description" content="{$pageDescription}">
  {/if}
{/block}

{* we'll move these around *}
{block "banner"}{/block}
{block "flashMessages"}{/block}

{block "content"}
  {assign var="declensionText" value=$declensionText|default:null}
  {assign var="tab" value=$tab|default:false}

  {include "banner/banner.tpl"}
  {include "bits/flashMessages.tpl"}

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" {if $tab == Constant::TAB_RESULTS}class="active"{/if}>
      <a
        href="#resultsTab"
        aria-controls="resultsTab"
        role="tab"
        data-toggle="tab"
        data-permalink="{$definitionLink}">
        {t}results{/t} ({$extra.numResults})
      </a>
    </li>

    {if $searchParams.paradigm}
      <li role="presentation" {if $tab == Constant::TAB_PARADIGM}class="active"{/if}>
        <a
          href="#paradigmTab"
          aria-controls="paradigmTab"
          role="tab"
          data-toggle="tab"
          data-permalink="{$paradigmLink}">
          {$declensionText}
        </a>
      </li>
    {/if}

    {if count($trees)}
      <li role="presentation" {if $tab == Constant::TAB_TREE}class="active"{/if}>
        <a
          href="#treeTab"
          aria-controls="treeTab"
          role="tab"
          data-toggle="tab"
          data-permalink="{$treeLink}">
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
            {if count($results)}
              {t
                count=$extra.numDefinitionsFullText
                1=$extra.numDefinitionsFullText
                plural="%1 definitions contain all the words"}
              One definition contains all the words{/t}
              {if $extra.numDefinitionsFullText > count($results)}
                {t 1=count($results)}(at most %1 shown){/t}
              {/if}
            {else}
              {t}No definitions contain all the words{/t}
            {/if}
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
            {capture "entryText"}
            {include "bits/entry.tpl" entry=$entries[0] variantList=true tagList=true}
            {/capture}

            {if count($results)}
              {t
                count=count($results)
                1=count($results)
                2=$smarty.capture.entryText
                plural="%1 definitions for %2"}
              One definition for %2{/t}
            {else}
              {t 1=$smarty.capture.entryText}No definitions for %1{/t}
            {/if}
          </h3>

          {include "search/missingDefinitionWarnings.tpl"}
          {include "search/definitionList.tpl"}
        {/if}

        {* regular expression search *}
      {elseif $searchType == $smarty.const.SEARCH_REGEXP}
        {capture "common"}
        {t}for{/t} <strong>{$cuv}</strong>
        {/capture}

        <h3>
          {if count($lexemes)}
            {t
              count=$extra.numLexemes
              1=$extra.numLexemes
              2=$cuv
              plural="%1 results for <strong>%2</strong>"}
            One result for <strong>%2</strong>{/t}
          {else}
            {t 1=$cuv}No results for <strong>%1</strong>{/t}
          {/if}

          {if $extra.numLexemes > count($lexemes)}
            {t 1=count($lexemes)}(at most %1 shown){/t}
          {/if}
        </h3>

        {if !count($lexemes) && $sourceId}
          {include "search/extendToAllSources.tpl"}
        {/if}

        {include "search/lexemeList.tpl"}

        {* normal search (inflected form search) *}
      {elseif $searchType == $smarty.const.SEARCH_INFLECTED}

        {include "search/gallery.tpl"}

        {if count($entries) > 1}
          <h3>
            {* this is always plural, but still needs to be localized *}
            {t count=count($entries) 1=count($entries) plural="%1 entries"}
            One entry{/t}
          </h3>

          {include "search/entryToc.tpl"}
        {else}
          {capture "entryText"}
          {include "bits/entry.tpl" entry=$entries[0] variantList=true tagList=true}
          {/capture}

          <h3>
            {if count($results)}
              {t
                count=$extra.numDefinitions
                1=$extra.numDefinitions
                2=$smarty.capture.entryText
                plural="%1 definitions for %2"}
              One definition for %2{/t}
            {else}
              {t 1=$smarty.capture.entryText}No definitions for %1{/t}
            {/if}

            {if $extra.numDefinitions > count($results)}
              {t 1=count($results)}(at most %1 shown){/t}
            {/if}
          </h3>

          {if !count($results) && count($entries) && $sourceId}
            {include "search/extendToAllSources.tpl"}
          {/if}
        {/if}

        {include "search/wikiArticles.tpl"}

        {* another <h3> for the definition list, if needed *}
        {if (count($entries) > 1) && count($results)}
          <h3>
            {t
              count=$extra.numDefinitions
              1=$extra.numDefinitions
              plural="%1 definitions"}
            One definition{/t}

            {if $extra.numDefinitions > count($results)}
              {t 1=count($results)}(at most %1 shown){/t}
            {/if}
          </h3>
        {/if}

        {include "search/missingDefinitionWarnings.tpl"}

        {include "search/showAllLink.tpl"}
        {include "search/definitionList.tpl"}
        {include "search/showAllLink.tpl"}

        {* multiword search *}
      {elseif $searchType == $smarty.const.SEARCH_MULTIWORD}
        <h3>
          {if count($results)}
            {t
              count=$extra.numDefinitions
              1=$extra.numDefinitions
              plural="%1 definitions match at least two words"}
            One definition matches at least two words{/t}
          {else}
            {t}No definitions match at least two words{/t}
          {/if}

          {if $extra.numDefinitions > count($results)}
            {t 1=count($results)}(at most %1 shown){/t}
          {/if}
        </h3>

        <p class="text-warning">
          {t}If the results are inadequate, you can look up individual words or you can search{/t}
          <a href="{Config::URL_PREFIX}text/{$cuv|escape:url}">{t}full-text{/t}</a>.
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
            {t escape="0" 1=$cuv}The word <strong>%1</strong> is not in the dictionary.
            Here are some suggestions:{/t}
          </h3>

          {include "search/entryList.tpl"}
        {else}
          <h3>{t escape="no" 1=$cuv}No results for <strong>%1</strong>{/t}</h3>
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
            {capture "text1"}{t}hide{/t}{/capture}
            {capture "text2"}{t}show{/t}{/capture}
          {else}
            {capture "text1"}{t}show{/t}{/capture}
            {capture "text2"}{t}hide{/t}{/capture}
          {/if}
          <div class="elisionLegend">
            * {t}elisions and long verb forms{/t} –
            <a id="toggleElision"
              href="#"
              class="doubleText"
              data-other-text="({$smarty.capture.text2})">
              ({$smarty.capture.text1})
            </a>
          </div>
        {/if}
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
