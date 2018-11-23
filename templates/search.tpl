{extends "layout.tpl"}

{block "title"}
  {$cuv|escape} - {'definition'|_}
  {if count($sourceList) == 1}{$sourceList[0]}{/if}
  {if $searchParams.paradigm}{'and paradigm'|_}{/if}
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
        {'results'|_} ({$extra.numResults})
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
          {'synthesis'|_} ({count($trees)})
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
        <h3>{'Definition with ID'|_} {$results|array_keys|implode}:</h3>

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
              none="{'No definitions contain'|_}"
              one="{'One definition contains'|_}"
              many="{'definitions contain'|_}"
              common="{'all the words'|_}"}
          </h3>

          {if !empty($extra.stopWords)}
            <p class="text-warning">
              {'The following words were ignored because they are too common:'|_}
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
          <h3>{'There is no entry with the given ID.'|_}</h3>
        {else}

          <h3>
            {include "bits/count.tpl"
              displayed=count($results)
              none="{'No definitions'|_}"
              one="{'One definition'|_}"
              many="{'definitions'|_}"
              common="{'for'|_}"}

            {include "bits/entry.tpl" entry=$entries[0] variantList=true tagList=true}
          </h3>

          {include "search/missingDefinitionWarnings.tpl"}
          {include "search/definitionList.tpl"}
        {/if}

        {* regular expression search *}
      {elseif $searchType == $smarty.const.SEARCH_REGEXP}
        {capture "common"}
        {'for'|_} <strong>{$cuv|escape}</strong>
        {/capture}

        <h3>
          {include "bits/count.tpl"
            displayed=count($lexemes)
            total=$extra.numLexemes|default:0
            none="{'No results'|_}"
            one="{'One result'|_}"
            many="{'results'|_}"
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
          <h3>{$entries|count} {'entries'|_}</h3>

          {include "search/entryToc.tpl"}
        {else}
          {capture "common"}
          {'for'|_} {include "bits/entry.tpl" entry=$entries[0] variantList=true tagList=true}
          {/capture}

          <h3>
            {include "bits/count.tpl"
              displayed=count($results)
              total=$extra.numDefinitions
              none="{'No definitions'|_}"
              one="{'One definition'|_}"
              many="{'definitions'|_}"
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
              one="{'One definition'|_}"
              many="{'definitions'|_}"
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
            none="{'No definitions match'|_}"
            one="{'One definition matches'|_}"
            many="{'definitions match'|_}"
            common="{'at least two words'|_}"}
        </h3>

        <p class="text-warning">
          {'If the results are inadequate, you can look up individual words or you can search'|_}
          <a href="{$wwwRoot}text/{$cuv|escape:url}">{'full-text'|_}</a>.
        </p>

        {if !empty($extra.ignoredWords)}
          <p class="text-warning">
            {'At most 5 words are allowed. The following words were ignored:'|_}
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
            * {'unrecommended or incorrect form'|_} –
            <a id="toggleNotRecommended"
              href="#"
              class="doubleText"
              data-other-text="({'hide'|_})">
              ({'show'|_})
            </a>
          </div>
        {/if}

        {if $hasElisionForms}
          <div class="elisionLegend">
            * {'elisions'|_} –
            <a id="toggleElision"
              href="#"
              class="doubleText"
              data-other-text="({'hide'|_})">
              ({'show'|_})
            </a>
          </div>
        {/if}

        <div class="paradigmLink voffset2">
          <a title="{'link to this inflected forms page'|_}"
            href="{$paradigmLink}">
            <i class="glyphicon glyphicon-link"></i>
            {'link to this paradigm'|_}
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
